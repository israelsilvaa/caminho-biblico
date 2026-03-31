<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sobre — <?= NOME_PROJETO ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="css/style.css">
<style>
.about-section {
    padding: 60px 0;
    max-width: 900px;
    margin: 0 auto;
}

.about-section h1 {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    color: #2E4057;
    text-align: center;
    margin-bottom: 40px;
}

.about-card {
    background: white;
    border-radius: 16px;
    padding: 40px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(46, 64, 87, 0.08);
}

.about-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    color: #2E4057;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #E8D5B7;
}

.about-card p {
    font-family: 'Lato', sans-serif;
    font-size: 1rem;
    line-height: 1.8;
    color: #5A6C7D;
    margin-bottom: 15px;
}

.about-card ul {
    font-family: 'Lato', sans-serif;
    font-size: 1rem;
    line-height: 1.8;
    color: #5A6C7D;
    margin-bottom: 15px;
    padding-left: 20px;
}

.about-card li {
    margin-bottom: 8px;
}

.tech-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.tech-item {
    background: #F8F9FA;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.tech-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(46, 64, 87, 0.12);
}

.tech-item i {
    font-size: 2rem;
    color: #C9A961;
    margin-bottom: 10px;
}

.tech-item h3 {
    font-family: 'Lato', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    color: #2E4057;
    margin-bottom: 5px;
}

.tech-item p {
    font-size: 0.85rem;
    color: #7A8C9D;
    margin: 0;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #C9A961;
    text-decoration: none;
    font-family: 'Lato', sans-serif;
    font-weight: 600;
    margin-bottom: 30px;
    transition: color 0.3s ease;
}

.back-link:hover {
    color: #B8944D;
}

.purpose-box {
    background: linear-gradient(135deg, #FFFAF0 0%, #FFF8E7 100%);
    border-left: 4px solid #C9A961;
    padding: 25px;
    border-radius: 8px;
    margin-top: 20px;
}

.purpose-box p {
    margin: 0;
    font-style: italic;
    color: #5A4A3A;
}

.highlight {
    color: #C9A961;
    font-weight: 600;
}

.feature-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 12px;
    margin-top: 20px;
}

.feature-box p {
    margin: 0;
    color: white;
}

.feature-box ul {
    color: white;
    margin-top: 15px;
}
</style>
</head>
<body>

<header class="hero" style="min-height: 40vh;">
  <div class="hero-bg"></div>
  <div class="container hero-content">
    <div class="hero-icon">✦</div>
    <h1><?= NOME_PROJETO ?></h1>
    <p class="hero-sub">Sobre o Projeto</p>
  </div>
</header>

<section class="about-section">
  <div class="container">

    <a href="index.php" class="back-link">
      <i class="fas fa-arrow-left"></i>
      Voltar ao Gerador de Planos
    </a>

    <div class="about-card">
      <h2><i class="fas fa-bullseye" style="color: #C9A961;"></i> Propósito</h2>
      <p>
        O <strong><?= NOME_PROJETO ?></strong> foi desenvolvido com o objetivo de ajudar cristãos a
        organizarem sua leitura bíblica de forma prática e personalizada. Muitas pessoas desejam
        ler a Bíblia completamente, mas não sabem por onde começar ou como se manter organizados.
      </p>
      <div class="purpose-box">
        <p>
          "Lâmpada para os meus pés é a tua palavra, e luz para o meu caminho." — Salmos 119:105
        </p>
      </div>
      <p style="margin-top: 20px;">
        Com esta ferramenta, você pode definir seu próprio ritmo de leitura, começar de qualquer
        ponto das Escrituras e acompanhar seu progresso dia após dia. O plano é gerado
        automaticamente e pode ser exportado para Excel com recursos avançados de acompanhamento:
        marcação de status, totais automáticos e gráfico visual de progresso.
      </p>
    </div>

    <div class="about-card">
      <h2><i class="fas fa-chart-pie" style="color: #C9A961;"></i> Acompanhamento de Progresso</h2>
      <p>
        O sistema inclui recursos avançados de acompanhamento de leitura, permitindo que você
        trackeie seu progresso diretamente no Excel:
      </p>
      <ul>
        <li><strong>✅ Coluna Status:</strong> Marque cada dia como "Lido" ou "Não lido" usando dropdowns interativos</li>
        <li><strong>📈 Totais Automáticos:</strong> Fórmulas COUNTIF atualizam os números em tempo real</li>
        <li><strong>📊 Gráfico de Pizza:</strong> Visualização automática do seu progresso com porcentagens</li>
        <li><strong>🎨 Layout Organizado:</strong> Seção "Resumo" destacada com totais e gráfico lado a lado</li>
        <li><strong>🔄 Atualização Dinâmica:</strong> Altere os Status e veja o gráfico atualizar automaticamente</li>
      </ul>
      <div class="feature-box">
        <p style="text-align: center; font-size: 1.1em; font-weight: 600;">
          <i class="fas fa-star"></i> Recurso Exclusivo: Gráfico Automático
        </p>
        <p style="text-align: center; margin-top: 10px;">
          Basta marcar os dias como "Lido" no dropdown e acompanhe seu progresso visual no gráfico de pizza!
        </p>
      </div>
    </div>

    <div class="about-card">
      <h2><i class="fas fa-lightbulb" style="color: #C9A961;"></i> Como Funciona</h2>
      <p>
        O sistema calcula automaticamente todos os capítulos da Bíblia (1189 ao total) e gera um
        plano de leitura baseado nas suas preferências:
      </p>
      <ul>
        <li><strong>Data de início:</strong> Quando você quer começar a ler</li>
        <li><strong>Capítulos por dia:</strong> De 1 a 20 (recomendado: 3-5)</li>
        <li><strong>Ponto de partida:</strong> Qual livro e capítulo você já leu</li>
      </ul>
      <p>
        O plano é gerado com datas automáticas e exportado para Excel com:
      </p>
      <ul>
        <li>Cores em zebra para facilitar leitura (Antigo e Novo Testamento)</li>
        <li>Coluna Status com dropdown "Lido/Não lido"</li>
        <li>Seção Resumo com totais automáticos (COUNTIF)</li>
        <li>Gráfico de pizza mostrando progresso com porcentagens</li>
        <li>Layout profissional e organizado</li>
      </ul>
    </div>

    <div class="about-card">
      <h2><i class="fas fa-code" style="color: #C9A961;"></i> Tecnologias Utilizadas</h2>
      <p>
        Este projeto foi desenvolvido com tecnologias modernas, focando em <span class="highlight">simplicidade</span>,
        <span class="highlight">performance</span> e <span class="highlight">compatibilidade</span>.
      </p>

      <div class="tech-grid">
        <div class="tech-item">
          <i class="fab fa-php"></i>
          <h3>PHP 7.4+</h3>
          <p>Lógica pura, sem frameworks</p>
        </div>
        <div class="tech-item">
          <i class="fas fa-file-excel"></i>
          <h3>PhpSpreadsheet</h3>
          <p>Excel com fórmulas e gráficos</p>
        </div>
        <div class="tech-item">
          <i class="fab fa-html5"></i>
          <h3>HTML5</h3>
          <p>Estrutura semântica</p>
        </div>
        <div class="tech-item">
          <i class="fab fa-css3-alt"></i>
          <h3>CSS3</h3>
          <p>Design responsivo moderno</p>
        </div>
        <div class="tech-item">
          <i class="fas fa-chart-pie"></i>
          <h3>Gráficos</h3>
          <p>Visualização de progresso</p>
        </div>
        <div class="tech-item">
          <i class="fas fa-font"></i>
          <h3>Google Fonts</h3>
          <p>Playfair Display & Lato</p>
        </div>
        <div class="tech-item">
          <i class="fab fa-font-awesome"></i>
          <h3>Font Awesome</h3>
          <p>Ícones vetoriais</p>
        </div>
      </div>
      <div style="background: #F8F9FA; padding: 15px; border-radius: 8px; margin-top: 20px;">
        <p style="margin: 0; font-size: 0.9rem; color: #5A6C7D;">
          <strong>PhpSpreadsheet</strong> é usado para gerar arquivos Excel profissionais com fórmulas COUNTIF,
          dropdowns de validação de dados e gráficos de pizza interativos.
        </p>
      </div>
    </div>

    <div class="about-card">
      <h2><i class="fas fa-star" style="color: #C9A961;"></i> Destaques Técnicos</h2>
      <ul>
        <li><strong>Arquitetura Modular:</strong> Código organizado em módulos independentes e reutilizáveis.</li>
        <li><strong>Excel Avançado:</strong> Usa PhpSpreadsheet para recursos profissionais (fórmulas, gráficos, validação).</li>
        <li><strong>Compatibilidade total:</strong> Funciona em qualquer hospedagem compartilhada com PHP 7.4 ou superior.</li>
        <li><strong>Performance:</strong> Código otimizado para carregamento rápido e experiência fluida.</li>
        <li><strong>Design responsivo:</strong> Interface que se adapta perfeitamente a desktop, tablet e mobile.</li>
        <li><strong>Código limpo:</strong> Estrutura organizada e fácil de manter/modificar.</li>
        <li><strong>Privacidade:</strong> Todos os dados são processados no servidor. Nenhuma informação pessoal é armazenada ou compartilhada.</li>
      </ul>
      <div style="background: #F8F9FA; padding: 15px; border-radius: 8px; margin-top: 15px;">
        <p style="margin: 0; font-size: 0.9rem; color: #5A6C7D;">
          <strong>Estrutura Modular:</strong> O sistema está organizado em módulos dentro da pasta <code>modules/</code>,
          facilitando a manutenção e evolução do código.
        </p>
      </div>
    </div>

    <div class="about-card">
      <h2><i class="fas fa-user" style="color: #C9A961;"></i> Desenvolvedor</h2>
      <p>
        Este projeto foi desenvolvido por <strong><?= DESENVOLVEDOR ?></strong>, desenvolvedor web
        apaixonado por criar ferramentas úteis e acessíveis.
      </p>
      <p>
        A ideia surgiu da necessidade pessoal de organizar a leitura bíblica de forma prática,
        sem depender de aplicativos complexos ou planilhas manuais.
      </p>
      <p>
        <em>"Que esta ferramenta seja uma bênção em sua jornada de leitura da Palavra de Deus."</em>
      </p>
    </div>

    <div class="about-card">
      <h2><i class="fas fa-code-branch" style="color: #C9A961;"></i> Código Aberto</h2>
      <p>
        O código fonte deste projeto está disponível no GitHub. Você pode contribuir, sugerir
        melhorias ou adaptar para suas necessidades.
      </p>
      <p style="text-align: center; margin-top: 20px;">
        <a href="https://github.com/israelsilvaa/caminho-biblico" target="_blank" style="display: inline-block; background: #2E4057; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background 0.3s ease;">
          <i class="fab fa-github"></i> Ver no GitHub
        </a>
      </p>
    </div>

  </div>
</section>

<footer class="site-footer">
  <div class="container footer-container">
    <p class="footer-verse">✦ <em>Lâmpada para os meus pés é a tua palavra — Sl 119:105</em></p>
    <p class="footer-dev">Desenvolvido por: <?= DESENVOLVEDOR ?></p>

    <div class="social-links">
      <a href="index.php" class="social-icon" title="Gerador de Planos">
        <i class="fas fa-home"></i>
      </a>
      <a href="sobre.php" class="social-icon" title="Sobre o projeto">
        <i class="fas fa-info-circle"></i>
      </a>
      <a href="https://www.instagram.com/israel_silvaaaa/" target="_blank" class="social-icon">
        <i class="fab fa-instagram"></i>
      </a>
      <a href="https://github.com/israelsilvaa/" target="_blank" class="social-icon">
        <i class="fab fa-github"></i>
      </a>
      <a href="https://www.linkedin.com/in/israel-silva-472b21214/" target="_blank" class="social-icon">
        <i class="fab fa-linkedin-in"></i>
      </a>
    </div>

    <span class="version"><?= VERSAO ?></span>
  </div>
</footer>

</body>
</html>
