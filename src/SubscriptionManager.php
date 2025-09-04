<?php

namespace Tok\MPSubscriptions;

class SubscriptionManager {
    private MercadoPagoService $service;
    private ErrorHandler $errorHandler;

    public function __construct(MercadoPagoService $service, ErrorHandler $errorHandler) {
        $this->service = $service;
        $this->errorHandler = $errorHandler;
    }

    public function criarAssinatura(string $nome, int $valor): ?string {
        try {
            $plano = $this->service->criarPlano([
                'nome' => $nome,
                'valor' => $valor,
            ]);
            return $plano['id'];
        } catch (\Throwable $e) {
            $this->errorHandler->handle($e);
            return null;
        }
    }
}
