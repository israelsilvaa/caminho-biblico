# 🗂️ Estrutura Modular - Caminho Bíblico

## 📁 Estrutura do Projeto

```
caminho-biblico/
├── 📄 index.php                          # Aplicação principal
├── 📄 config.php                        # Configurações
├── 📄 sobre.php                          # Página sobre o projeto
├── 📁 css/
│   └── style.css
│
├── 📁 modules/                          # MÓDULOS DO SISTEMA
│   ├── XlsxWriterV4_Fixed.php          # Gerador Excel (Status + Totais + Gráfico)
│   └── XlsxWriterV4_Fixed_V1.php        # Backup da versão inicial
│
├── 📁 tests/                            # Testes
│   └── teste_v4_completo.php           # Teste do módulo
│
├── 📁 vendor/                           # PhpSpreadsheet (Composer)
├── 📄 composer.json                     # Dependências
├── 📄 MODULOS.md                        # Este arquivo
└── 📄 README.md                         # Documentação principal
```

---

## 🎯 Módulo XlsxWriterV4_Fixed

### Funcionalidades

Este módulo gera arquivos Excel (.xlsx) com recursos avançados de acompanhamento de progresso:

- ✅ **Coluna Status** com dropdown "Lido / Não lido"
- ✅ **Fórmulas COUNTIF** automáticas para totais
- ✅ **Seção Resumo** com totais destacados
- ✅ **Gráfico de pizza** com porcentagens
- ✅ **Layout fixo e organizado**
- ✅ **Cores em zebra** para facilitar leitura

### Layout do Excel

```
┌─────────┬─────────┬────────────────┬──────┬─────────┬─────┬─────┐
│   A     │    B    │       C        │  D   │    E    │  F  │  G  │
├─────────┼─────────┼────────────────┼──────┼─────────┼─────┼─────┤
│ (vazio) │ Título mergeado (4 colunas)    │         │     │     │
├─────────┴─────────┴────────────────┴──────┼─────────┼─────┼─────┤
│         │ Subtítulo mergeado             │         │     │     │
├─────────┼─────────┼────────────────┼──────┼─────────┼─────┼─────┤
│    #    │  Data   │    Leitura     │Test. │ Status  │Resumo(merge)│ ← Linha 4
├─────────┼─────────┼────────────────┼──────┼─────────┼─────┼─────┤
│    1    │01/04/26 │ Gênesis 1-5    │ A.T. │dropdown │Lidos│ =0  │ ← Linha 5
│    2    │02/04/26 │ Gênesis 6-10   │ A.T. │dropdown │Não  │ =0  │ ← Linha 6
│   ...   │  ...    │      ...       │ ...  │  ...    │lidos│     │
└─────────┴─────────┴────────────────┴──────┴─────────┴─────┴─────┘
                                                             ↓
                                                  ┌──────────┴─────────┐
                                                  │   Gráfico de Pizza  │ ← Coluna I
                                                  │   com Porcentagens  │
                                                  └─────────────────────┘
```

### Como Usar

```php
require_once __DIR__ . '/modules/XlsxWriterV4_Fixed.php';

$x = new XlsxWriterV4_Fixed();
$x->addSheet('Plano de Leitura');

// Configurar larguras das colunas
$x->setColWidth('A', 7);
$x->setColWidth('B', 13);
$x->setColWidth('C', 56);
$x->setColWidth('D', 8);

// Escrever título mergeado
$x->writeRowMerged('Plano de Leitura Bíblica', 1, 4, 'title');
$x->writeRowMerged('5 Capítulos por dia', 1, 4, 'sub');
$x->writeRowMerged('', 1, 4, 'sub');

// Escrever cabeçalho
$x->writeRow([
    ['#', 'header'],
    ['Data', 'header'],
    ['Leitura', 'header'],
    ['Test.', 'header'],
]);

// Marcar início dos dados
$x->markDataStart();

// Escrever dados (com zebra)
$x->writeRow([[1, 'zebra_even'], ['01/04/2026', 'zebra_even'], ['Gênesis 1-5', 'zebra_even'], ['A.T.', 'zebra_even']]);
$x->writeRow([[2, 'zebra_odd'], ['02/04/2026', 'zebra_odd'], ['Gênesis 6-10', 'zebra_odd'], ['A.T.', 'zebra_odd']]);

// Marcar fim dos dados
$x->markDataEnd();

// Adicionar recursos de acompanhamento
$x->addStatusColumnFixed('Não lido');     // Coluna Status com dropdown
$x->addTotalsRowFixed();                   // Totais COUNTIF
$x->addPieChartFixed();                    // Gráfico de pizza

// Baixar arquivo
$x->download('plano_leitura.xlsx');
```

### Métodos Disponíveis

#### `addSheet(string $name)`
Define o nome da planilha.

#### `setColWidth(string $col, float $width)`
Define a largura de uma coluna.

#### `writeRow(array $cells, string $defStyle = 'body')`
Escreve uma linha de dados.
- Pode passar apenas valores: `['Dia', 'Data', 'Leitura']`
- Ou valores com estilos: `[['valor', 'header'], ['valor', 'body']]`

#### `writeRowMerged(string $value, int $fromCol, int $toCol, string $style)`
Escreve uma linha mergeada (células combinadas).

#### `markDataStart()`
Marca o início dos dados (deve ser chamado antes de escrever as linhas de dados).

#### `markDataEnd()`
Marca o fim dos dados (deve ser chamado após escrever todas as linhas).

#### `addStatusColumnFixed(string $defaultValue = 'Não lido')`
Adiciona coluna Status com dropdown.
- Título "Status" na linha do cabeçalho
- "Resumo" mesclado na mesma linha
- Dropdowns "Lido/Não lido" nas linhas de dados

#### `addTotalsRowFixed()`
Adiciona totais com COUNTIF.
- "Lidos:" + fórmula COUNTIF
- "Não lidos:" + fórmula COUNTIF

#### `addPieChartFixed()`
Adiciona gráfico de pizza.
- Dados auxiliares em colunas J/K (ocultas)
- Gráfico posicionado na coluna I
- Porcentagens nas fatias
- Cores personalizadas

#### `download(string $filename)`
Gera e baixa o arquivo Excel.

#### `getBytes(): string`
Retorna o conteúdo do arquivo como string (útil para salvar em disco).

### Estilos Disponíveis

- `'title'` - Título principal (negrito, 14pt, cor escura)
- `'sub'` - Subtítulo (itálico, 9pt, cinza)
- `'header'` - Cabeçalho da tabela (negrito, fundo azul claro)
- `'zebra_even'` - Linhas pares (fundo azul muito claro)
- `'zebra_odd'` - Linhas ímpares (fundo azul médio)
- `'body'` - Corpo padrão (fundo branco)
- `'legend'` - Legenda (itálico, 8pt, cinza)

### Layout Fixo

O módulo usa posições fixas das células:

- **E4**: Cabeçalho "Status"
- **F4:G4**: "Resumo" (mesclado)
- **E5, E6, E7...**: Dropdowns de status
- **F5/G5**: "Lidos:" / COUNTIF
- **F6/G6**: "Não lidos:" / COUNTIF
- **J/K**: Colunas ocultas (dados do gráfico)
- **I8:T26**: Gráfico de pizza

---

## 📦 Dependências

### PhpSpreadsheet

O módulo utiliza a biblioteca **PhpSpreadsheet** para gerar arquivos Excel com recursos avançados.

**Instalação:**
```bash
composer require phpoffice/phpspreadsheet
```

**Requisitos:**
- PHP 7.4 ou superior
- Extensões PHP: `zip`, `xml`

---

## 🧪 Testes

O arquivo `tests/teste_v4_completo.php` contém um teste completo do módulo.

**Executar teste:**
```bash
php tests/teste_v4_completo.php
```

Ou acesse pelo navegador:
```
http://localhost/caminho-biblico/tests/teste_v4_completo.php
```

---

## 🔧 Manutenção

### Backup

O arquivo `XlsxWriterV4_Fixed_V1.php` é um backup da versão inicial do módulo.

### Atualizações

Para modificar o módulo:
1. Faça backup do arquivo atual
2. Implemente as mudanças
3. Teste completamente
4. Atualize esta documentação

---

## 📚 Recursos Adicionais

- [Documentação PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/)
- [README do Projeto](README.md)
- [Página Sobre](sobre.php)

---

**Última atualização:** Março 2026
