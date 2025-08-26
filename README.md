# Tok Mercado Pago Subscriptions

**Versão:** 1.0.0  
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
- [Desenvolvimento](#desenvolvimento)  

---

## Descrição

O plugin **Tok Mercado Pago Subscriptions** permite criar e gerenciar planos de assinatura personalizados, integrando o WordPress com o Mercado Pago e Melhor Envio. Ele foi desenvolvido seguindo boas práticas de OOP (Programação Orientada a Objetos) e separação de responsabilidades, garantindo código limpo e fácil manutenção.

---

## Funcionalidades

- Integração com API do Mercado Pago para:
  - Criação de planos e assinaturas.
  - Processamento de pagamentos e notificações.
- Página de configuração no WordPress Admin para:
  - Inserir chaves do Firebase e Mercado Pago.
  - Gerenciar outras configurações do plugin.
- Estrutura modular com classes separadas para Admin e Mercado Pago.
- Uso de autoload via Composer.

---

## Requisitos

- WordPress 5.0 ou superior  
- PHP 7.4 ou superior  
- Composer (para autoload das dependências)  
- Conta ativa no Mercado Pago

---

## Instalação

1. Faça o download do plugin ou clone o repositório na pasta `wp-content/plugins/`.
2. Instale as dependências do Composer:
   ```bash
   composer install
3. Ative o plugin através do painel de Plugins no WordPress.

---

## Configuração

Acesse Configurações > Mercado Pago Subscriptions no painel do WordPress.

Preencha os campos:

...
...
...

Clique em Salvar Configurações.

---

## Desenvolvimento

O plugin segue uma estrutura modular baseada em namespaces:

- Tok\MPSubscriptions\Plugin – Classe principal e ponto de entrada.
- Tok\MPSubscriptions\Admin – Gerencia a interface de administração e configurações.
- Tok\MPSubscriptions\MercadoPago – Integração com a API do Mercado Pago.