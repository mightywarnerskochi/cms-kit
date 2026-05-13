<?php

namespace CMS\SiteManager\Http\Controllers\CmsKit;

use CMS\SiteManager\Models\CmsKit\Language;
use CMS\SiteManager\Services\StaticTranslationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;

class LanguageStaticTextController extends Controller
{
    public function __construct(
        protected StaticTranslationService $staticTranslations
    ) {}

    public function index()
    {
        $languages = Language::query()->orderBy('name')->get();
        $masterCode = $this->staticTranslations->masterCode();
        $directory = $this->staticTranslations->directory();

        return view('cms-kit::languages.static-texts.index', compact('languages', 'masterCode', 'directory'));
    }

    public function edit(string $code)
    {
        $code = strtolower($code);
        $language = Language::query()->whereRaw('LOWER(code) = ?', [$code])->firstOrFail();
        $masterCode = $this->staticTranslations->masterCode();
        $isMaster = $code === $masterCode;

        $data = $this->staticTranslations->read($code);
        $flat = $this->staticTranslations->flatten($data);
        if (is_array(old('entries'))) {
            $fromOld = [];
            foreach (old('entries') as $e) {
                if (!is_array($e)) {
                    continue;
                }
                $k = trim((string) ($e['key'] ?? ''));
                if ($k === '') {
                    continue;
                }
                $fromOld[$k] = (string) ($e['value'] ?? '');
            }
            if ($fromOld !== []) {
                $flat = $fromOld;
            }
        }
        ksort($flat);

        $jsonFilePath = str_replace('\\', '/', $this->staticTranslations->pathForCode(strtolower((string) $language->code)));

        return view('cms-kit::languages.static-texts.edit', compact('language', 'flat', 'isMaster', 'masterCode', 'jsonFilePath'));
    }

    public function update(Request $request, string $code)
    {
        $code = strtolower($code);
        Language::query()->whereRaw('LOWER(code) = ?', [$code])->firstOrFail();

        $masterCode = $this->staticTranslations->masterCode();
        $entries = $request->input('entries', []);
        if (!is_array($entries)) {
            $entries = [];
        }

        $normalized = [];
        $seenKeys = [];
        foreach ($entries as $row) {
            if (!is_array($row)) {
                continue;
            }
            $key = trim((string) ($row['key'] ?? ''));
            if ($key === '') {
                continue;
            }
            if (isset($seenKeys[$key])) {
                throw ValidationException::withMessages(['entries' => ['Duplicate key in form: ' . $key]]);
            }
            $seenKeys[$key] = true;

            $val = $row['value'] ?? '';
            if (is_string($val)) {
                $normalized[$key] = $val;
            } elseif ($val === null) {
                $normalized[$key] = '';
            } elseif (is_scalar($val)) {
                $normalized[$key] = (string) $val;
            } else {
                $normalized[$key] = json_encode($val, JSON_UNESCAPED_UNICODE);
            }
        }

        if ($code === $masterCode) {
            $this->validateEnglishKeys($normalized);
            $this->assertMasterKeysMatchFile($normalized);
            $tree = $this->staticTranslations->unflatten($normalized);
            $this->staticTranslations->write($code, $tree);

            foreach (Language::query()->get() as $lang) {
                $other = strtolower((string) $lang->code);
                if ($other !== $masterCode) {
                    $this->staticTranslations->read($other);
                }
            }

            return redirect()
                ->route('cms.languages.static-texts.edit', $code)
                ->with('success', 'English (master) static texts saved. Other languages were synchronized with any new keys.');
        }

        $english = $this->staticTranslations->readMaster();
        $errors = $this->staticTranslations->validateNonEnglishMatchesEnglish($english, $normalized);
        if ($errors !== []) {
            throw ValidationException::withMessages(['entries' => $errors]);
        }

        $tree = $this->staticTranslations->unflatten($normalized);
        $tree = $this->staticTranslations->mergeWithMaster($english, $tree);
        $this->staticTranslations->write($code, $tree);

        return redirect()
            ->route('cms.languages.static-texts.edit', $code)
            ->with('success', 'Static texts saved for ' . strtoupper($code) . '.');
    }

    /**
     * @param  array<string, string>  $normalized
     */
    protected function validateEnglishKeys(array $normalized): void
    {
        $pattern = '/^[a-zA-Z0-9]([a-zA-Z0-9._-]*[a-zA-Z0-9])?$/';
        $errors = [];
        foreach (array_keys($normalized) as $key) {
            if (str_contains((string) $key, '..')) {
                $errors[] = 'Key cannot contain empty segments: ' . $key;

                continue;
            }
            if (!preg_match($pattern, (string) $key)) {
                $errors[] = 'Invalid key format: ' . $key;
            }
        }
        if ($errors !== []) {
            throw ValidationException::withMessages(['entries' => $errors]);
        }
    }

    /**
     * Master saves may only update values for keys already present on disk (keys are maintained in development).
     *
     * @param  array<string, string>  $normalized
     */
    protected function assertMasterKeysMatchFile(array $normalized): void
    {
        $masterFlat = $this->staticTranslations->flatten($this->staticTranslations->readMaster());
        $onDisk = array_keys($masterFlat);
        $submitted = array_keys($normalized);
        sort($onDisk);
        sort($submitted);
        if ($onDisk === $submitted) {
            return;
        }
        throw ValidationException::withMessages([
            'entries' => [
                'Keys must match the master JSON file exactly. Add or remove keys in the codebase, reload this page, then edit values.',
            ],
        ]);
    }
}
