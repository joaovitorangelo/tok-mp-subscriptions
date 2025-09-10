# Tok Mercado Pago Subscriptions

**Versão:** 1.1.0  
**Autor:** Tok Digital  
**Descrição:** Integração com Mercado Pago e Melhor Envio para criação de planos de assinatura personalizados no WordPress.

---

## Tabela de Conteúdos

- [Descrição](#descrição)  
- [Funcionalidades](#funcionalidades)  
- [Requisitos](#requisitos)  
- [Instalação](#instalação)  
- [Configuração](#configuração)  
- [Uso](#uso)  
- [Estrutura de Arquivos](#estrutura-de-arquivos)  
- [Desenvolvimento](#desenvolvimento)  
- [Changelog](#changelog)   

---

## Descrição

O plugin **Tok Mercado Pago Subscriptions** permite criar e gerenciar planos de assinatura personalizados, integrando o WordPress com o Mercado Pago e Melhor Envio. Ele foi desenvolvido seguindo boas práticas de OOP (Programação Orientada a Objetos) e separação de responsabilidades, garantindo código limpo e fácil manutenção.

---

## Funcionalidades

- Integração com API do **Mercado Pago**:
  - Criação de planos e assinaturas.
- Integração com API do **Melhor Envio**:
  - Cotação de frete.
  - Criação de etiquetas de envio.
- Página de configuração no WordPress Admin:
  - Inserção de chaves do Mercado Pago e Melhor Envio.
  - Gerenciamento de outras configurações do plugin.
- Suporte a **Elementor Forms** para integração com front-end.
- Autoload via **Composer**.

---

## Requisitos

- WordPress 5.0 ou superior  
- PHP 7.4 ou superior  
- Composer (para autoload das dependências)  
- Conta ativa no Mercado Pago  
- Conta ativa no Melhor Envio  
- Elementor e Elementor PRO

---

## Instalação

1. Faça o download do plugin ou clone o repositório na pasta `wp-content/plugins/`.
2. Instale as dependências do Composer:
+    ```bash
+    composer install
+    ```
3. Ative o plugin através do painel de Plugins no WordPress.

---

## Configuração

Acesse Configurações > Mercado Pago Subscriptions no painel do WordPress.

Preencha os campos:

MP_PUBLIC_KEY  
MP_ACCESS_TOKEN  
ME_ACCESS_TOKEN  

Clique em Salvar Configurações.

---

## Estrutura de Arquivos

```
tok-mp-subscriptions
├── .env
├── .gitignore
├── CHANGELOG.md
├── composer.json
├── logs
│   ├── calculate_shipping.log
│   ├── create_plan.log
│   └── crypto.log
├── README.md
├── src
│   ├── Admin.php
│   ├── Core
│   │   ├── PostTypes
│   │   │   ├── CustomPostType.php
│   │   │   ├── Plan.php
│   │   │   ├── Subscription.php
│   │   │   └── Taxonomy.php
│   │   ├── Security
│   │   │   └── Crypto.php
│   │   └── Services
│   │       ├── MelhorEnvio.php
│   │       └── MercadoPago.php
│   ├── Frontend
│   │   ├── assets
│   │   │   ├── css
│   │   │   └── js
│   │   ├── Forms.php
│   │   └── Handlers
│   │       └── TokSubscriptionsPlanForm.php
│   ├── Infrastructure
│   │   └── HttpClient.php
│   └── Plugin.php
├── tok-mp-subscriptions.php
└── vendor
    ├── autoload.php
    └── composer
        ├── autoload_classmap.php
        ├── autoload_namespaces.php
        ├── autoload_psr4.php
        ├── autoload_real.php
        ├── autoload_static.php
        ├── ClassLoader.php
        └── LICENSE
```

Breve descrição das pastas principais:

- Core/PostTypes/ → Custom Post Types e Taxonomias (Plan, Subscription, etc).
- Core/Security/ → Criptografia e decriptação de dados sensíveis (Crypto.php).
- Core/Services/ → Serviços de negócio (Mercado Pago, Melhor Envio).
- Frontend/ → Integração com Elementor Forms e assets JS/CSS.
- Frontend/Handlers/ → Handlers para processar formulários do front-end.
- Infrastructure/ → Client HTTP genérico para APIs externas (HttpClient.php).
- Admin.php → Configurações do plugin no WP Admin.
- Plugin.php → Classe principal, inicializa todo o plugin.

---

## Desenvolvimento

O plugin segue uma estrutura modular baseada em namespaces:

- Tok\MPSubscriptions\Core\PostTypes\CustomPostType – Classe genérica para criar CPTs.
- Tok\MPSubscriptions\Core\PostTypes\Plan – CPT Planos de Assinatura.
- Tok\MPSubscriptions\Core\PostTypes\Subscription – CPT Assinaturas.
- Tok\MPSubscriptions\Core\PostTypes\Taxonomy – Criação de taxonomias.
- Tok\MPSubscriptions\Core\Security\Crypto – Criptografia de credenciais.
- Tok\MPSubscriptions\Core\Services\MercadoPago – Integração com API Mercado Pago.
- Tok\MPSubscriptions\Core\Services\MelhorEnvio – Integração com API Melhor Envio.
- Tok\MPSubscriptions\Frontend\Forms – Ponto de entrada para Elementor Forms.
- Tok\MPSubscriptions\Frontend\Handlers\TokSubscriptionsPlanForm – Exemplo de handler de formulário.
- Tok\MPSubscriptions\Infrastructure\HttpClient – Centraliza chamadas HTTP externas.
- Tok\MPSubscriptions\Plugin – Classe principal do plugin.
- Tok\MPSubscriptions\Admin – Admin settings e interface de configuração.

## Changelog
[Changelog](CHANGELOG.md)