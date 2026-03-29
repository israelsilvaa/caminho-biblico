# 📖 Caminho Bíblico

Gerador de plano de leitura bíblica personalizado. Crie seu plano de leitura com a quantidade de capítulos por dia que preferir, começando de qualquer ponto da Bíblia.

## ✨ Funcionalidades

- 🎯 **Plano personalizado**: Escolha quantos capítulos ler por dia (1-20)
- 📅 **Data de início personalizada**: Comece quando quiser
- 📍 **Comece de qualquer ponto**: Selecione o livro e capítulo onde você está
- 📊 **Acompanhamento visual**: Zebra colorida no Excel para facilitar a leitura
- 📥 **Exportação para Excel**: Baixe seu plano completo em formato .xlsx
- 📱 **Design responsivo**: Funciona perfeitamente em desktop, tablet e mobile
- 🎨 **Interface elegante**: Design moderno com cores sépias e douradas

## 🚀 Como Executar

### Requisitos

- PHP 7.4 ou superior
- Navegador web moderno

### Passos

1. **Navegue até a pasta htdocs:**
   ```bash
   cd caminho/biblico/v4/caminho-biblico/htdocs
   ```

2. **Inicie o servidor PHP embutido:**
   ```bash
   php -S localhost:8000
   ```

3. **Abra seu navegador:**
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
5. **Acompanhe seu progresso** na tabela interativa
6. **Baixe o Excel** para ter seu plano completo offline

## 📊 Estrutura do Projeto

```
htdocs/
├── index.php          # Página principal e geração do plano
├── sobre.php          # Página sobre o projeto
├── config.php         # Configurações centralizadas
├── XlsxWriter.php     # Classe para gerar arquivos Excel
├── css/
│   └── style.css      # Estilos da aplicação
└── README.md          # Este arquivo
```

## 🎨 Tecnologias Utilizadas

- **PHP 7.4+** - Lógica pura, sem frameworks
- **HTML5** - Estrutura semântica
- **CSS3** - Design responsivo moderno
- **JavaScript** - Interações de interface
- **Excel Nativo** - Geração de .xlsx em PHP puro (sem dependências)
- **Google Fonts** - Tipografia (Playfair Display & Lato)
- **Font Awesome** - Ícones

## 🔧 Características Técnicas

- ✅ **Sem dependências externas** - Excel gerado 100% em PHP puro
- ✅ **Compatibilidade total** - Funciona em qualquer hospedagem compartilhada
- ✅ **Performance otimizada** - Código limpo e eficiente
- ✅ **Código organizado** - Fácil de manter e estender
- ✅ **Privacidade** - Nenhum dado pessoal é armazenado

## 📝 Formato do Excel

O arquivo Excel gerado contém:

- **Título e informações do plano** (data início, fim, total de dias)
- **Tabela completa** com todos os dias de leitura
- **Cores em zebra** para facilitar a leitura
  - Linhas pares: `#EEF3FF` (azul claro)
  - Linhas ímpares: `#E0EAFF` (azul médio)
- **Colunas**: Dia, Data, Leitura, Testamento

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
