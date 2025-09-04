<?php

namespace Tok\MPSubscriptions\Core\Services\Payloads;

class PayloadBuilderFactory {
    public static function make(string $service): PayloadBuilderInterface {
        return match ($service) {
            'melhor_envio' => new MelhorEnvioPayloadBuilder(),
            'mercado_pago' => new MercadoPagoPayloadBuilder(),
            default => throw new \InvalidArgumentException("Builder para {$service} não encontrado")
        };
    }
}
