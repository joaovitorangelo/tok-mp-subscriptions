# Changelog

## [1.0.5] - 2025-09-04
- Implementado AWS SQS para filas de processamento de falhas críticas (ex.: criação de planos de assinatura).
- Integração com AWS Secrets Manager para armazenar de forma segura as credenciais do SQS.
- Atualizado HttpClient para:
    Capturar erros de requisições HTTP e respostas de API.
    Enviar automaticamente jobs de falha para a fila SQS.
    Registrar erros no Sentry para monitoramento centralizado.
- Removida necessidade de expor Access Key e Secret Key diretamente no código.
- Preparado para consumo de jobs via AWS Lambda, permitindo retry/backoff automático.
- Estrutura de logging e fallback profissional, mantendo o plugin seguro e escalável.

## [1.0.4] - 2025-09-03
- Monitoramento de erros com Sentry
- Ajustes para que as classes de serviço (MelhorEnvioPayloadBuilder, PayloadBuilderFactory) sejam utilizadas corretamente na estrutura do plugin.
- Organização de payloads para integração com Melhor Envio e tratamento de dados enviados pelo frontend.
- Implementação de captura de dados do formulário Elementor e envio via AJAX (#form-field-cep, #form-field-post_id).
- Criação de funções de manipulação de formulários Elementor para registrar assinaturas e dados dos usuários.
- Criação de endpoints AJAX para cálculo de frete no Melhor Envio (handle_calculate_shipping) para usuários logados e não logados.
- Organização de arquivos separada por responsabilidades

## [1.0.3] - 2025-09-02
- Criptografar credenciais de acesso
- Registrar taxonomias e metaboxes dinamicamente

## [1.0.2] - 2025-08-28
- Criação de Planos de Assinatura
- Integração com o Mercado Pago
- Campos para armazenar a Public Key e Access Token do Mercado Pago

## [1.0.1] - 2025-08-27
- Adicionada classe `HttpClient` para centralizar e simplificar chamadas HTTP (GET e POST) à API do Mercado Pago e Melhor Envio
- Integração com o Melhor Envio
- Ajustes no carregamento do plugin
- Correção de autoload

## [1.0.0] - 2025-08-20
- Versão inicial do plugin
