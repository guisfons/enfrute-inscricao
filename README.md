# Inscrição Enfrute (tema)

Tema de **apresentação** do [SciFlow WP](https://github.com/guisfons/SciFlow-WP): página de inscrição (WooCommerce), submissão, painéis de autor, revisor e editor, detalhe do trabalho, pôsteres, palestras, certificados e inscrição de terceiros.

## Requisitos
- WordPress 6.0+ (testado no 7.1), PHP 7.4+.
- **Plugin SciFlow WP 1.1.0+** (obrigatório para os fluxos do evento). Sem ele, o tema funciona, mostra um aviso no painel e exibe "indisponível" nas páginas do evento.
- WooCommerce (recomendado): inscrição, checkout com CPF/CNPJ, PayPal para estrangeiros, inscrição de terceiros. Sem ele não há erro fatal; esses recursos ficam desativados.

## Instalação
1. Instale e ative o plugin SciFlow WP.
2. Aparência → Temas → Enviar → `enfrute-inscricao-<versão>.zip` → Ativar.
3. SciFlow → Diagnóstico → **Criar páginas ausentes**: as páginas são criadas com os modelos deste tema.
4. Aparência → Personalizar → Identidade do site: logo (o padrão é `assets/images/logo.webp`, que leva à página inicial).
5. Aparência → Menus: posições `primary`, `inscritos`, `palestrantes`, `editor_dashboard`, `reviewer_dashboard` e `tecnicos`. O menu muda conforme o perfil do usuário logado.

## Modelos de página
Submissão de Artigo · Listagem de Submissões · Dashboard do Editor · Dashboard do Revisor · Detalhes do Artigo · Trabalhos Publicados · Inscrição – Início · Inscrição de Terceiros · Baixar Certificado · Submissão de Palestra · Pôsteres Públicos.

As URLs entre páginas vêm do plugin (`sciflow_get_page_url()`), não de slugs fixos.

## Integração com o plugin
- `add_theme_support('sciflow')` informa ao plugin que o tema fornece os modelos.
- `inc/sciflow-integration.php`: detecta plugin e WooCommerce, gera URLs e protege os templates.
- Regras de acesso e de "inscrição paga" são delegadas ao plugin.

## Bibliotecas
Bootstrap 5.3.2, Bootstrap Icons 1.11.3 e jQuery Mask 1.14.16, servidas localmente de `assets/vendor/` (MIT, integridade conferida; ver `assets/vendor/README.md`).

## Distribuição
Cada merge na `main` publica automaticamente uma **Release** com o ZIP do tema (veja [docs/RELEASES.md](docs/RELEASES.md)).

`bin/build.sh` gera `dist/enfrute-inscricao-<versão>.zip` sem `.git`, `docs/` nem fontes SCSS.

Documentação completa do produto (arquitetura, segurança, operação) no repositório do plugin, em `docs/`.

## Licença
MIT.
