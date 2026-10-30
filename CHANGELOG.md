# Changelog

## 1.2.0 — 2026-10-30
### Novidades
- Página inicial: aviso "Certificados disponíveis" com o botão para emitir na área do participante (Minha conta → Certificados). Logada, a pessoa vê quantos certificados tem; o aviso só aparece quando há certificados publicados. Requer o SciFlow WP 1.2.2 ou superior; com versões anteriores o aviso não aparece.


## 1.1.1 — 2026-10-25
### Correções
- build: checksum com nome do arquivo sem caminho


## 1.1.0 — 2026-10-25
### Segurança
- **Detalhe do trabalho:** visitantes anônimos podiam ver trabalhos sem revisor atribuído (ID 0 = meta padrão 0). O acesso agora passa por `sciflow_can_view_submission()`, com escopo por evento.
- **Inscrição de terceiros:** produto e cupom validados no servidor (só o produto de inscrição e cupons de inscritor).
- **Certificados:** o download foi delegado ao plugin (diretório protegido, modo de acesso, limite de tentativas).
- Bibliotecas servidas localmente (sem CDN); jQuery duplicado removido.
### Portabilidade
- Sem erro fatal quando o WooCommerce ou o plugin SciFlow estão ausentes; aviso administrativo.
- URLs por função de página (sem `/meus-artigos`, `/editor/dashboard/`… fixos); logo e link configuráveis.
- `add_theme_support('sciflow')`, `custom-logo`; text domain unificado em `inscricao-enfrute`.
- Cabeçalho com requisitos (WP 6.0+, PHP 7.4+) e licença.

## 1.0.0
Versão anterior. Veja o histórico Git.
