<?php

namespace App\Services;

use App\Models\Product;

class ProductChangeClassifier
{
    public function classify(Product $product, array $originalValues): ?string
    {
        $originalStatus      = $originalValues['status'] ?? null;
        $originalConsumption = $originalValues['consumption'] ?? null;

        if ($product->status === 'cancelado' && $originalStatus === 'ativo') {
            return 'churn';
        }

        if ($product->status === 'ativo' && $originalStatus === 'cancelado') {
            return 'reactivation';
        }

        if ($originalConsumption !== null) {
            $delta = (float) $product->consumption - (float) $originalConsumption;

            if ($delta > 0) {
                return 'upgrade';
            }

            if ($delta < 0) {
                return 'downgrade';
            }
        }

        return null;
    }

    public function deltaConsumption(Product $product, array $originalValues): float
    {
        $originalConsumption = (float) ($originalValues['consumption'] ?? $product->consumption);

        return (float) $product->consumption - $originalConsumption;
    }
}
