<?php

namespace CMS\SiteManager\Support;

use Illuminate\Validation\ValidationException;

trait ManagesOrderIndex
{
    protected function resolveOrderForCreate(string $modelClass, ?int $requestedOrder): int
    {
        $total = $modelClass::count();
        $maxAllowed = $total + 1;

        if ($requestedOrder === null) {
            return $maxAllowed;
        }

        if ($requestedOrder < 1 || $requestedOrder > $maxAllowed) {
            throw ValidationException::withMessages([
                'order_index' => "Order must be between 1 and {$maxAllowed}.",
            ]);
        }

        return $requestedOrder;
    }

    protected function resolveOrderForReorder(string $modelClass, int $requestedOrder): int
    {
        $total = $modelClass::count();

        if ($total <= 1) {
            return 1;
        }

        if ($requestedOrder < 1 || $requestedOrder > $total) {
            throw ValidationException::withMessages([
                'order_index' => "Order must be between 1 and {$total}.",
            ]);
        }

        return $requestedOrder;
    }

    protected function normalizeOrderIndex(string $modelClass): void
    {
        $items = $modelClass::orderBy('order_index')->orderBy('id')->get(['id']);

        $position = 1;
        foreach ($items as $item) {
            $modelClass::whereKey($item->id)->update(['order_index' => $position]);
            $position++;
        }
    }
}
