# 📖 Caminho Bíblico

Gerador de plano de leitura bíblica personalizado com acompanhamento de progresso. Crie seu plano de leitura com a quantidade de capítulos por dia que preferir, começando de qualquer ponto da Bíblia, e acompanhe sua evolução com gráficos automáticos.

## ✨ Funcionalidades

### 📋 Geração de Planos
- 🎯 **Plano personalizado**: Escolha quantos capítulos ler por dia (1-20)
- 📅 **Data de início personalizada**: Comece quando quiser
- 📍 **Comece de qualquer ponto**: Selecione o livro e capítulo onde você está
- 🧮 **Cálculo automático**: 1189 capítulos da Bíblia organizados automaticamente
- 📊 **Testamento AT/NT**: Identificação visual de Antigo e Novo Testamento

### 📊 Acompanhamento de Progresso
- ✅ **Coluna Status**: Marque cada dia como "Lido" ou "Não lido" com dropdown interativo
- 📈 **Totais automáticos**: Fórmulas COUNTIF dinâmicas mostrando lidos/não lidos
- 📊 **Gráfico de pizza**: Visualização automática do progresso com porcentagens
- 🎨 **Layout organizado**: Seção "Resumo" destacada com totais e gráfico lado a lado
- 🔄 **Atualização dinâmica**: Altere os Status e veja o gráfico atualizar automaticamente

### 📥 Exportação Excel
- Formato .xlsx nativo com PhpSpreadsheet
- Cores em zebra para facilitar leitura (Antigo e Novo Testamento)
- Dropdowns de validação de dados
- Fórmulas Excel que atualizam em tempo real
- Gráficos de pizza interativos
- Layout profissional e organizado

### 🎨 Interface
- 📱 **Design responsivo**: Funciona em desktop, tablet e mobile
- 🎨 **Design elegante**: Cores sépias e douradas
- ⚡ **Performance**: Carregamento rápido
- 🌐 **Código limpo**: Estrutura modular e organizada
- 📈 **Barra de progresso**: Acompanhe estatísticas do plano

## 🚀 Como Executar

### Requisitos

- PHP 7.4 ou superior
- Composer (para PhpSpreadsheet)
- Navegador web moderno

### Instalação

1. **Clone o repositório:**
   ```bash
   git clone https://github.com/israelsilvaa/caminho-biblico.git
   cd caminho-biblico
   ```

2. **Instale as dependências:**
   ```bash
   composer install
   ```

3. **Inicie o servidor PHP:**
   ```bash
   php -S localhost:8000
   ```

4. **Abra seu navegador:**
   ```
   http://localhost:8000
   ```

## 📖 Como Usar

1. **Selecione a data de início** - Escolha quando você quer começar a ler
2. **Defina capítulos por dia** - Use os botões +/- para ajustar (recomendado: 3-5)
3. **Escolha seu ponto de partida**:
   - Selecione o livro onde você está
   - Escolha o capítulo atual desse livro
4. **Clique em "Gerar Plano"**
5. **Acompanhe seu progresso** na tabela interativa com estatísticas
6. **Baixe o Excel** com todos os recursos de acompanhamento

### Usando o Excel

1. Abra o arquivo `.xlsx` baixado
2. **Marque seu progresso**: Na coluna "Status", use o dropdown para selecionar "Lido" ou "Não lido"
3. **Veja os totais atualizarem**: As células F5/G5 e F6/G6 mostram quantidade de lidos/não lidos
4. **Acompanhe o gráfico**: O gráfico de pizza na coluna I mostra seu progresso visual com porcentagens

## 📊 Estrutura do Projeto

```
caminho-biblico/
├── index.php                    # Aplicação principal
├── sobre.php                    # Página sobre o projeto
├── config.php                   # Configurações
│
├── modules/                     # Módulos do sistema
│   └── XlsxWriterV4_Fixed.php  # Gerador Excel (Status + Totais + Gráfico)
│
├── css/
│   └── style.css                # Estilos
├── vendor/                      # PhpSpreadsheet (Composer)
├── composer.json                # Dependências
├── MODULOS.md                   # Documentação dos módulos
└── README.md                    # Este arquivo
```

## 🎨 Tecnologias Utilizadas

- **PHP 7.4+** - Lógica pura, sem frameworks
- **PhpSpreadsheet** - Biblioteca para Excel com fórmulas e gráficos
- **HTML5** - Estrutura semântica
- **CSS3** - Design responsivo moderno
- **JavaScript** - Interações de interface
- **Google Fonts** - Tipografia (Playfair Display & Lato)
- **Font Awesome** - Ícones

## 🔧 Características Técnicas

### Arquitetura Modular
- ✅ **Módulos independentes** - Código organizado em classes reutilizáveis
- ✅ **Estrutura limpa** - Fácil manutenção e evolução
- ✅ **Separação de responsabilidades** - Lógica, apresentação e dados separados

### Recursos Excel Avançados
- ✅ **PhpSpreadsheet** - Biblioteca robusta para manipulação de Excel
- ✅ **Fórmulas COUNTIF** - Cálculos dinâmicos de totais
- ✅ **Dropdowns de validação** - Entrada de dados controlada
- ✅ **Gráficos de pizza** - Visualização de dados automática
- ✅ **Colunas ocultas** - Dados auxiliares invisíveis para o gráfico
- ✅ **Porcentagens** - Labels com porcentagem nas fatias do gráfico

### Performance e Compatibilidade
- ✅ **Compatibilidade total** - Funciona em hospedagem compartilhada
- ✅ **Performance otimizada** - Código limpo e eficiente
- ✅ **Design responsivo** - Adapta-se a qualquer dispositivo
- ✅ **Privacidade** - Nenhum dado pessoal é armazenado

## 📝 Layout do Excel

O arquivo Excel gerado possui o seguinte layout fixo:

**Cabeçalho:**
- A1:D3 = Título e subtítulo mergeados
- A4:D4 = Cabeçalho da tabela (#, Data, Leitura, Test.)

**Seção de Acompanhamento:**
- E4 = "Status" (cabeçalho da coluna)
- F4:G4 = "Resumo" (célula mergeada)

**Dados e Status:**
- A5:D∞ = Dados do plano (Dia, Data, Leitura, Testamento)
- E5:E∞ = Dropdowns "Lido/Não lido"

**Totais Automáticos:**
- F5 = "Lidos:"
- G5 = `=COUNTIF(E5:E...,"Lido")`
- F6 = "Não lidos:"
- G6 = `=COUNTIF(E5:E...,"Não lido")`

**Gráfico:**
- J/K = Colunas ocultas (dados auxiliares do gráfico)
- I8:T26 = Gráfico de pizza com porcentagens

## 📊 Funcionalidades Detalhadas

### Coluna Status
- **Dropdown interativo**: Selecione "Lido" ou "Não lido" em cada dia
- **Validação de dados**: Garante entrada consistente
- **Formatação condicional**: Destaque visual para status

### Totais Automáticos
- **Fórmulas COUNTIF**: Contam automaticamente lidos/não lidos
- **Atualização em tempo real**: Basta alterar o status
- **Cálculo preciso**: Sempre atualizado

### Gráfico de Pizza
- **Visualização automática**: Mostra proporção de lidos vs não lidos
- **Porcentagens**: Exibe % diretamente nas fatias
- **Cores personalizadas**: Azul para lidos, cinza para não lidos
- **Posicionamento fixo**: Layout organizado e profissional

## 🤝 Contribuindo

Este projeto é open source! Sinta-se à vontade para:

1. Fazer fork do projeto
2. Criar uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abrir um Pull Request

## 📄 Licença

Este projeto foi desenvolvido por **Israel Silva** e está disponível para uso pessoal e educacional.

## 🌐 Links

- **GitHub**: [https://github.com/israelsilvaa/](https://github.com/israelsilvaa/)
- **LinkedIn**: [https://linkedin.com/in/israel-silva-472b21214/](https://linkedin.com/in/israel-silva-472b21214/)
- **Instagram**: [https://instagram.com/israel_silvaaaa/](https://instagram.com/israel_silvaaaa/)

---

<div align="center">

**"Lâmpada para os meus pés é a tua palavra — Sl 119:105**

Feito com 💜 e muito ☕

</div>
