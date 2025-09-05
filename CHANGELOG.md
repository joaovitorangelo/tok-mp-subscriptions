# Changelog

## [1.0.8] - 2025-09-05
Webhook Mercado Pago
- Implementado Webhook para receber notificações de assinaturas (preapproval).
- Apenas assinaturas autorizadas são registradas no CPT Subscription.
- Salva código, valor, status, e-mail do pagador e ID do plano vinculado.
- Garante que o sistema só armazene assinaturas efetivamente pagas, sem depender do redirecionamento do usuário.
Notificando usuário via Push Notifications e E-mail após criação do Plano de assinatura.
Integração Mercado Pago
- Ajustado o fluxo de criação de assinaturas vinculadas a planos.
- Agora, ao criar um plano (preapproval_plan), o init_point é retornado para o usuário completar o checkout.
- Removida a tentativa de criar assinatura via API com card_token_id para status pending, evitando erro card_token_id is required.
- O builder de assinatura (mercado_pago_subscription) foi descartado, já que não é necessário criar assinatura manualmente se não houver pagamento imediato.
Payloads e Builders
- Simplificado o payload de assinatura para usar apenas:
- preapproval_plan_id
- payer_email
- back_url (opcional)
Removidos campos desnecessários (auto_recurring, external_reference, status) quando não há cartão do cliente.
Fluxo do formulário
- O método SubscriptionsPlanForm::process agora:
- Cria o plano via create_plan.
- Retorna o init_point do plano para redirecionar o usuário ao checkout.
- Calcula frete e média de preço antes de criar o plano.
Reduzida complexidade, evitando múltiplas chamadas à API para assinatura sem necessidade.

## [1.0.7] - 2025-09-05
Ajustes na estrutura do plugin

## [1.0.6] - 2025-09-05
Ajustes na estrutura do plugin

## [1.0.5] - 2025-09-04
Implementado AWS SQS para filas de processamento de falhas críticas (ex.: criação de planos de assinatura).
Integração com AWS Secrets Manager para armazenar de forma segura as credenciais do SQS.
Atualizado HttpClient para:
 - Capturar erros de requisições HTTP e respostas de API.
 - Enviar automaticamente jobs de falha para a fila SQS.
 - Registrar erros no Sentry para monitoramento centralizado.
Removida necessidade de expor Access Key e Secret Key diretamente no código.
Preparado para consumo de jobs via AWS Lambda, permitindo retry/backoff automático.
Estrutura de logging e fallback profissional, mantendo o plugin seguro e escalável.

## [1.0.4] - 2025-09-03
Monitoramento de erros com Sentry
Ajustes para que as classes de serviço (MelhorEnvioPayloadBuilder, PayloadBuilderFactory) sejam utilizadas corretamente na estrutura do plugin.
Organização de payloads para integração com Melhor Envio e tratamento de dados enviados pelo frontend.
Implementação de captura de dados do formulário Elementor e envio via AJAX (#form-field-cep, #form-field-post_id).
Criação de funções de manipulação de formulários Elementor para registrar assinaturas e dados dos usuários.
Criação de endpoints AJAX para cálculo de frete no Melhor Envio (handle_calculate_shipping) para usuários logados e não logados.
Organização de arquivos separada por responsabilidades

## [1.0.3] - 2025-09-02
Criptografar credenciais de acesso
Registrar taxonomias e metaboxes dinamicamente

## [1.0.2] - 2025-08-28
Criação de Planos de Assinatura
Integração com o Mercado Pago
Campos para armazenar a Public Key e Access Token do Mercado Pago

## [1.0.1] - 2025-08-27
Adicionada classe `HttpClient` para centralizar e simplificar chamadas HTTP (GET e POST) à API do Mercado Pago e Melhor Envio
Integração com o Melhor Envio
Ajustes no carregamento do plugin
Correção de autoload

## [1.0.0] - 2025-08-20
Versão inicial do plugin
