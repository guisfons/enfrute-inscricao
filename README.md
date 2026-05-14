# Tema Enfrute Inscrição

Este é um tema WordPress personalizado desenvolvido para o evento Enfrute, com foco em gestão de inscrições, trabalhos científicos e eventos relacionados.

## Estrutura

- **`assets/scss/`**: Arquivos SCSS compilados.
- **`assets/images/`**: Imagens e ícones do tema.
- **`inc/`**: Arquivos PHP de funcionalidades:
    - `class-sciflow-roles.php`: Definição de papéis de usuário e permissões.
    - `class-sciflow-forms.php`: Gerenciamento de formulários de inscrição e trabalhos.
    - `class-sciflow-admin.php`: Customizações do painel administrativo.
- **`template-parts/`**: Componentes de template.
- **`woocommerce/`**: Overrides de templates WooCommerce.

## Funcionalidades

- **Papéis Personalizados**: Inscrito, Palestrante, Revisor, Editor, Semco Editor, Semco Revisor, Enfrute Editor, Enfrute Revisor, Gestor Técnico Epagri.
- **Formulários de Inscrição**: Formulários dinâmicos para diferentes perfis de usuários.
- **Dashboard de Usuário**: Área logada para gerenciamento de inscrições e trabalhos.
- **Gestão Técnica**: Módulo dedicado para técnicos da Epagri com controle de acesso e relatórios.
- **Integração WooCommerce**: Gerenciamento de pagamentos e produtos (ingressos/inscrições).
- **Custom Post Types**: `semco_trabalho`, `enfrute_trabalho`, `sciflow_palestra`.

## Instalação

1. Clone ou baixe o repositório para a pasta `wp-content/themes/enfrute-inscricao`.
2. Instale as dependências PHP (via Composer) se necessário.
3. Ative o tema no painel do WordPress.