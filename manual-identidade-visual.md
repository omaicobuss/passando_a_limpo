# Manual de Identidade Visual

## 1. Direção visual

Este sistema adota uma linguagem visual inspirada no website Finview, traduzida para o contexto de participação cívica, eleições e acompanhamento de propostas públicas.

A referência principal não foi copiada literalmente. O objetivo foi absorver a lógica visual do produto:

- hero amplo com contraste forte;
- cartões editoriais com muito espaço interno;
- leitura orientada por comparação e tomada de decisão;
- hierarquia tipográfica forte;
- combinação de verde profundo, dourado e superfícies claras;
- interface com aparência confiável, informativa e contemporânea.

## 2. Personalidade da marca

Palavras-chave da marca:

- confiável;
- clara;
- editorial;
- institucional sem rigidez;
- moderna;
- orientada à comparação;
- legível em desktop e mobile.

## 3. Paleta cromática

### Cores principais

- Primária: `#074C3E`
  Uso: ações principais, links ativos, destaques institucionais, navegação.

- Primária forte: `#03211B`
  Uso: hover, fundos escuros, áreas de grande contraste.

- Texto principal: `#222E48`
  Uso: títulos, dados e informações críticas.

- Texto secundário: `#6A7283`
  Uso: descrições, apoio visual, metadados.

### Cores de apoio

- Acento: `#FCB650`
  Uso: realces, números de etapas, marcadores e pequenos pontos de atenção.

- Acento suave: `#FFF1D6`
  Uso: chips, pills, etiquetas e fundos sutis.

- Superfície: `#FFFFFF`
  Uso: cartões, formulários, tabelas e módulos principais.

- Fundo geral: `#F7F5EF`
  Uso: base da interface.

- Superfície suave: `#F4EFE3`
  Uso: fundos auxiliares e blocos secundários.

- Borda: `#E5DFD1`
  Uso: divisões discretas, inputs, cartões e tabelas.

## 4. Tipografia

### Fonte principal

- Família: Jost
- Pesos recomendados: 400, 500, 600, 700, 800

### Regras tipográficas

- Títulos devem usar peso 700 ou 800.
- Títulos principais devem ter leve compressão visual com tracking negativo.
- Corpo de texto usa peso 400 ou 500.
- Navegação, botões e badges usam 600.
- Labels e microcopy em destaque podem usar caixa alta com espaçamento adicional.

## 5. Grid, espaçamento e proporção

### Container

- Largura máxima recomendada: `1320px`

### Raios

- Grande: `30px`
- Médio: `22px`
- Pequeno: `16px`
- Botões e navegação: formato cápsula (`999px`)

### Espaçamentos

Escala recomendada:

- 8px
- 12px
- 16px
- 24px
- 32px
- 48px
- 64px

### Sombra

- Sombra principal: `0 22px 60px rgba(34, 46, 72, 0.12)`
- Sombra suave: `0 12px 32px rgba(34, 46, 72, 0.08)`

## 6. Estrutura visual da interface

### Header

O cabeçalho deve transmitir produto maduro e confiável.

Regras:

- topline escura com mensagem contextual;
- navbar clara com efeito translúcido;
- marca com selo compacto e texto institucional;
- links em cápsulas com estado ativo suave;
- CTA de login/cadastro ou saída sempre visível.

### Hero

O hero da página inicial deve seguir estas características:

- fundo escuro em gradiente verde profundo;
- tipografia grande e dominante;
- subtítulo com contraste alto, mas menos intenso que o título;
- bloco lateral com métricas e painéis complementares;
- pequenos elementos flutuantes para sensação de produto premium.

### Cartões

Os cartões são a peça central do sistema.

Regras:

- superfícies claras;
- borda sutil;
- sombra macia;
- muito respiro interno;
- títulos fortes;
- metadados com cor secundária;
- CTA simples e direto.

### Tabelas

As tabelas devem parecer módulos premium, não planilhas cruas.

Regras:

- encapsular em superfície branca;
- cabeçalho com fundo suave;
- linhas com contraste baixo;
- espaçamento generoso;
- paginação em formato pílula.

### Formulários

Regras:

- campos altos e arredondados;
- borda suave;
- foco com halo translúcido da cor primária;
- labels fortes e legíveis;
- blocos de formulário dentro de cartões ou superfícies bem definidas.

## 7. Componentes-chave

### Botões

- Primário: verde profundo com sombra.
- Secundário: contorno com fundo claro.
- Em fundos escuros: botão claro sólido ou contorno branco translúcido.

### Badges e chips

- Devem ser compactos, com tipografia 600.
- Preferir fundos claros e discretos.
- Estados críticos podem usar verde, amarelo e vermelho do Bootstrap, desde que harmonizados com o restante do tema.

### Breadcrumbs

- Devem aparecer como cápsula clara translúcida.
- Nunca competir visualmente com o título da página.

### Alerts

- Bordas suaves;
- sem aparência de caixa técnica antiga;
- foco em legibilidade e feedback imediato.

## 8. Imagens, ilustração e ícones

A referência trabalha com vetores e imagens de apoio em áreas estratégicas. No sistema atual, a mesma ideia deve ser aplicada com moderação:

- usar blocos de cor, badges e painéis quando não houver ilustração disponível;
- preferir ícones simples e consistentes;
- evitar excesso de imagens meramente decorativas;
- sempre priorizar informação e leitura.

## 9. Movimento

O movimento deve ser discreto e funcional.

Padrões recomendados:

- entrada suave de páginas (`fade + translateY`);
- hover curto em cartões e botões;
- sem animações contínuas distrativas;
- transições entre 180ms e 250ms.

## 10. Acessibilidade

Regras mínimas:

- contraste alto para títulos e ações principais;
- alvos de clique confortáveis;
- formulários com foco visível;
- conteúdo legível em mobile;
- nunca usar cor como único indicador de estado.

## 11. Aplicação prática no Passando a Limpo

A implementação atual usa esta identidade visual em:

- cabeçalho e rodapé globais;
- home com hero, serviços, destaques e CTA;
- cartões, tabelas, inputs, paginação e alerts;
- páginas internas de listagem, conta e autenticação por meio de estilização global.

## 12. Regras para futuras telas

Ao criar novas telas, seguir este checklist:

- usar Jost como fonte principal;
- priorizar fundo claro com superfícies brancas;
- reservar o verde profundo para navegação e ações principais;
- usar o dourado apenas como destaque pontual;
- organizar conteúdo em cartões amplos;
- manter hierarquia editorial: eyebrow, título forte, texto de apoio, CTA;
- evitar blocos densos sem respiro;
- preservar coerência com o hero e com o footer já implementados.
