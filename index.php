<?php
session_start();
error_reporting(E_ALL);
error_reporting(E_ALL & ~E_WARNING);
ini_set('display_errors', 1);

// ═══════════════════════════════════════════════════════
//  CONFIGURAÇÕES
// ═══════════════════════════════════════════════════════
require_once __DIR__ . '/config.php';

// ═══════════════════════════════════════════════════════
//  CAMINHO BÍBLICO — Gerador de Plano Público
//  Excel gerado em PHP puro (sem shell_exec, sem Composer)
// ═══════════════════════════════════════════════════════

$LIVROS = [
    ["Gênesis",50],["Êxodo",40],["Levítico",27],["Números",36],
    ["Deuteronômio",34],["Josué",24],["Juízes",21],["Rute",4],
    ["1 Samuel",31],["2 Samuel",24],["1 Reis",22],["2 Reis",25],
    ["1 Crônicas",29],["2 Crônicas",36],["Esdras",10],["Neemias",13],
    ["Ester",10],["Jó",42],["Salmos",150],["Provérbios",31],
    ["Eclesiastes",12],["Cânticos",8],["Isaías",66],["Jeremias",52],
    ["Lamentações",5],["Ezequiel",48],["Daniel",12],["Oséias",14],
    ["Joel",3],["Amós",9],["Obadias",1],["Jonas",4],["Miquéias",7],
    ["Naum",3],["Habacuque",3],["Sofonias",3],["Ageu",2],
    ["Zacarias",14],["Malaquias",4],
    ["Mateus",28],["Marcos",16],["Lucas",24],["João",21],["Atos",28],
    ["Romanos",16],["1 Coríntios",16],["2 Coríntios",13],["Gálatas",6],
    ["Efésios",6],["Filipenses",4],["Colossenses",4],
    ["1 Tessalonicenses",5],["2 Tessalonicenses",3],["1 Timóteo",6],
    ["2 Timóteo",4],["Tito",3],["Filemom",1],["Hebreus",13],
    ["Tiago",5],["1 Pedro",5],["2 Pedro",3],["1 João",5],
    ["2 João",1],["3 João",1],["Judas",1],["Apocalipse",22],
];

$NT_SET = array_flip(["Mateus","Marcos","Lucas","João","Atos","Romanos",
    "1 Coríntios","2 Coríntios","Gálatas","Efésios","Filipenses",
    "Colossenses","1 Tessalonicenses","2 Tessalonicenses","1 Timóteo",
    "2 Timóteo","Tito","Filemom","Hebreus","Tiago","1 Pedro",
    "2 Pedro","1 João","2 João","3 João","Judas","Apocalipse"]);

// Mapa acumulado para o seletor de livros
$MAPA = [];
$acc = 0;
foreach ($LIVROS as [$livro, $total]) {
    $MAPA[] = ['livro' => $livro, 'caps' => $total, 'inicio_global' => $acc];
    $acc += $total;
}
$TOTAL_CAPS = $acc; // 1189

// ─── Funções do plano ────────────────────────────────────────────────

function gerarCapitulos(array $livros): array {
    $caps = [];
    foreach ($livros as [$livro, $total])
        for ($c = 1; $c <= $total; $c++)
            $caps[] = [$livro, $c];
    return $caps;
}

function formatarLeitura(array $batch): string {
    $grupos = [];
    $la = null;
    $ia = null;
    $fa = null;

    foreach ($batch as [$l, $c]) {
        if ($la === null) {
            // primeira iteração
            $la = $l;
            $ia = $c;
            $fa = $c;
            continue;
        }

        if ($l === $la) {
            $fa = $c;
        } else {
            $grupos[] = ($ia === $fa) ? "$la $ia" : "$la $ia–$fa";
            $la = $l;
            $ia = $c;
            $fa = $c;
        }
    }

    if ($la !== null) {
        $grupos[] = ($ia === $fa) ? "$la $ia" : "$la $ia–$fa";
    }

    return implode(' + ', $grupos);
}

function gerarPlano(array $livros, int $capsDia, int $capsLidos, DateTime $dataInicio): array {
    $caps      = gerarCapitulos($livros);
    $restantes = array_slice($caps, $capsLidos);
    $plano     = []; $dia = 0;
    for ($i = 0; $i < count($restantes); $i += $capsDia) {
        $batch = array_slice($restantes, $i, $capsDia);
        $data  = clone $dataInicio;
        $data->modify("+{$dia} days");
        $plano[] = [clone $data, formatarLeitura($batch)];
        $dia++;
    }
    return $plano;
}

function isNt(string $leitura, array $ntSet): bool {
    foreach (array_keys($ntSet) as $l)
        if (str_contains($leitura, $l)) return true;
    return false;
}

// ─── Download XLSX ───────────────────────────────────────────────────
if (($_GET['action'] ?? '') === 'download' && isset($_SESSION['plano_params'])) {
    require_once __DIR__ . '/XlsxWriter.php';
    $p  = $_SESSION['plano_params'];
    $dt = DateTime::createFromFormat('Y-m-d', $p['data_inicio']);
    $plano = gerarPlano($LIVROS, $p['caps_dia'], $p['caps_lidos'], $dt);
    gerarXlsx($plano, $p, $NT_SET);
    exit;
}

// ─── Formulário POST ─────────────────────────────────────────────────
$plano = null; $erro = ''; $form = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'download') {
    $data_raw  = trim($_POST['data_inicio'] ?? '');
    $caps_dia  = max(1, min(20, (int)($_POST['caps_dia']  ?? 5)));
    $livro_idx = max(0, min(count($MAPA)-1, (int)($_POST['livro_idx'] ?? 0)));
    $cap_livro = max(1, (int)($_POST['cap_livro'] ?? 1));
    $form      = compact('data_raw','caps_dia','livro_idx','cap_livro');

    $dt = DateTime::createFromFormat('Y-m-d', $data_raw);
    if (!$dt) {
        $erro = 'Data inválida. Verifique o formato.';
    } else {
        $info      = $MAPA[$livro_idx];
        $cap_livro = max(1, min($info['caps'], $cap_livro));
        // caps_lidos = capítulos antes do livro selecionado + (cap_livro - 1)
        $caps_lidos = $info['inicio_global'] + ($cap_livro - 1);

        $plano = gerarPlano($LIVROS, $caps_dia, $caps_lidos, $dt);

        $_SESSION['plano_params'] = [
            'data_inicio' => $data_raw,
            'caps_dia'    => $caps_dia,
            'caps_lidos'  => $caps_lidos,
            'livro_nome'  => $info['livro'],
            'cap_livro'   => $cap_livro,
            'inicio_fmt'  => $dt->format('d/m/Y'),
        ];
    }
}

// ─── Paginação / progresso ───────────────────────────────────────────
$hoje = new DateTime(); $hoje->setTime(0,0,0);
$pagina = max(1, (int)($_POST['p'] ?? $_GET['p'] ?? 1));
$por_pagina = 50;

$total_dias = $dia_hoje_idx = $dias_passados = 0;
$data_fim_str = '—'; $progresso = 0;
$total_pag = 1; $slice = [];

if ($plano) {
    $total_dias = count($plano);
    $data_fim_str = $plano[$total_dias-1][0]->format('d/m/Y');
    $dia_hoje_idx = -1;
    foreach ($plano as $i => [$d]) {
        $dc = clone $d; $dc->setTime(0,0,0);
        if ($dc < $hoje) $dias_passados++;
        if ($dc == $hoje && $dia_hoje_idx < 0) $dia_hoje_idx = $i;
    }
    $progresso = $total_dias ? round($dias_passados/$total_dias*100,1) : 0;
    $total_pag  = max(1,(int)ceil($total_dias/$por_pagina));
    $pagina     = min($pagina, $total_pag);
    $slice      = array_slice($plano, ($pagina-1)*$por_pagina, $por_pagina);
}

// ─── Gerar XLSX ──────────────────────────────────────────────────────
function gerarXlsx(array $plano, array $p, array $ntSet): void {
    $x = new XlsxWriter();
    $x->addSheet('Plano de Leitura');
    $x->setColWidth(1, 7);
    $x->setColWidth(2, 13);
    $x->setColWidth(3, 56);
    $x->setColWidth(4, 8);

    $total   = count($plano);
    $dataFim = $total ? $plano[$total-1][0]->format('d/m/Y') : '—';
    $livroInfo = "{$p['livro_nome']} cap. {$p['cap_livro']}";

    // Linha 1: título
    $x->writeRowMerged(
        "Plano de Leitura Bíblica — {$p['caps_dia']} Capítulos por Dia",
        1, 4, 'title'
    );
    // Linha 2: subtítulo
    $x->writeRowMerged(
        "Início: {$p['inicio_fmt']}  |  Término: {$dataFim}  |  Total: {$total} dias  |  Começa em: {$livroInfo}",
        1, 4, 'sub'
    );
    // Linha 3: espaço
    $x->writeRowMerged('', 1, 4, 'sub');

    // Cabeçalho
    $x->writeRow([
        ['#',          'header'],
        ['Data',       'header'],
        ['Leitura',    'header'],
        ['Test.',      'header'],
    ]);

    // Dados
    foreach ($plano as $i => [$data, $leitura]) {
        $nt  = isNt($leitura, $ntSet);
        $par = $i % 2 === 0;

        $style = $par ? 'zebra_even' : 'zebra_odd';

        $x->writeRow([
            [$i+1,                   $style],
            [$data->format('d/m/Y'), $style],
            [$leitura,               $style],
            [$nt ? 'N.T.' : 'A.T.',  $style],
        ]);
    }

    // Legenda
    $x->writeRowMerged('', 1, 4, 'legend');
    $x->writeRowMerged(
        'Legenda:  Cores em zebra para facilitar a leitura (Azul claro para AT e NT)',
        1, 4, 'legend'
    );

    $x->download('plano_leitura_biblica.xlsx');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= NOME_PROJETO ?> — Gerador de Plano</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="hero">
  <div class="hero-bg"></div>
  <div class="container hero-content">
    <div class="hero-icon">✦</div>
    <h1><?= NOME_PROJETO ?></h1>
    <p class="hero-sub">Gere seu plano de leitura personalizado</p>
  </div>
</header>

<!-- FORMULÁRIO -->
<section class="form-section">
  <div class="container">
    <form method="post" action="" id="formPlano">
      <div class="form-card">
        <h2 class="form-title">Configure seu plano</h2>

        <?php if ($erro): ?>
          <div class="alert-error">⚠ <?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <div class="form-grid">

          <div class="form-group">
            <label for="data_inicio">Data de início</label>
            <input type="date" id="data_inicio" name="data_inicio"
                   value="<?= htmlspecialchars($form['data_raw'] ?? date('Y-m-d')) ?>" required>
            <span class="form-hint">A partir de quando quer começar a leitura</span>
          </div>

          <div class="form-group">
            <label>Capítulos por dia</label>
            <div class="caps-wrap">
              <button type="button" class="caps-btn" onclick="ajustar(-1)">−</button>
              <input type="number" id="caps_dia" name="caps_dia"
                     value="<?= (int)($form['caps_dia'] ?? 5) ?>" min="1" max="20" readonly>
              <button type="button" class="caps-btn" onclick="ajustar(1)">+</button>
            </div>
            <span class="form-hint">De 1 a 20 capítulos por dia</span>
          </div>

          <div class="form-group">
            <label for="livro_idx">Já leu até… (livro)</label>
            <select id="livro_idx" name="livro_idx" onchange="atualizarCaps()">
              <?php foreach ($MAPA as $i => $info): ?>
              <option value="<?= $i ?>"
                <?= ($form['livro_idx'] ?? 0) == $i ? 'selected' : '' ?>>
                <?= htmlspecialchars($info['livro']) ?> (<?= $info['caps'] ?> cap.)
              </option>
              <?php endforeach; ?>
            </select>
            <span class="form-hint">Escolha "Gênesis" se ainda não começou</span>
          </div>

          <div class="form-group">
            <label for="cap_livro">Capítulo atual nesse livro</label>
            <select id="cap_livro" name="cap_livro">
              <?php
              $li = (int)($form['livro_idx'] ?? 0);
              for ($c = 1; $c <= $MAPA[$li]['caps']; $c++):
              ?>
              <option value="<?= $c ?>" <?= ($form['cap_livro'] ?? 1) == $c ? 'selected' : '' ?>>
                Capítulo <?= $c ?>
              </option>
              <?php endfor; ?>
            </select>
            <span class="form-hint">O plano começa a partir deste capítulo</span>
          </div>

        </div>

        <div class="form-footer">
          <button type="submit" name="p" value="1" class="btn-primary">
            ✦ Gerar Plano
          </button>
          <button type="button" onclick="location.href='index.php'" class="btn-secondary">
            ↻ Limpar
          </button>
        </div>
      </div>
    </form>
  </div>
</section>

<?php if ($plano): ?>

<!-- STATS -->
<section class="stats-bar">
  <div class="container stats-grid">
    <div class="stat">
      <span class="stat-val"><?= $total_dias ?></span>
      <span class="stat-lbl">dias de leitura</span>
    </div>
    <div class="stat">
      <span class="stat-val"><?= $form['caps_dia'] ?></span>
      <span class="stat-lbl">cap. por dia</span>
    </div>
    <div class="stat">
      <span class="stat-val"><?= $data_fim_str ?></span>
      <span class="stat-lbl">conclusão prevista</span>
    </div>
    <div class="stat">
      <span class="stat-val"><?= $progresso ?>%</span>
      <span class="stat-lbl">concluído</span>
    </div>
  </div>
</section>

<?php if ($progresso > 0): ?>
<section class="progress-section">
  <div class="container">
    <div class="progress-header">
      <span class="progress-label">Progresso</span>
      <span class="progress-pct"><?= $dias_passados ?> / <?= $total_dias ?> dias</span>
    </div>
    <div class="progress-bar">
      <div class="progress-fill" style="width:<?= $progresso ?>%"></div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- TABELA + DOWNLOAD -->
<section class="main-section">
  <div class="container">

    <div class="download-bar">
      <a href="?action=download" class="btn-download" target="_blank">
        ↓ Baixar Excel (.xlsx)
      </a>
      <span class="download-hint">Planilha completa com todos os <?= $total_dias ?> dias</span>
    </div>

    <?php if ($dia_hoje_idx >= 0): ?>
    <?php [$dd, $dl] = $plano[$dia_hoje_idx]; ?>
    <div class="today-card">
      <div class="today-badge">📖 Leitura de Hoje</div>
      <div class="today-date"><?= $dd->format('d/m/Y') ?> · Dia <?= $dia_hoje_idx+1 ?></div>
      <div class="today-leitura"><?= htmlspecialchars($dl) ?></div>
    </div>
    <?php endif; ?>

    <div class="reading-table" id="tabela">
      <div class="table-head">
        <span>Dia</span>
        <span>Data</span>
        <span>Leitura</span>
        <span>Test.</span>
      </div>
      <?php foreach ($slice as $k => [$data, $leitura]):
        $dc = clone $data; $dc->setTime(0,0,0);
        $lido  = $dc < $hoje;
        $eh_hj = $dc == $hoje;
        $nt    = isNt($leitura, $NT_SET);
        $num   = ($pagina-1)*$por_pagina + $k + 1;
        $cls   = 'row ' . ($nt ? 'row-nt' : 'row-at')
               . ($lido  ? ' row-lido' : '')
               . ($eh_hj ? ' row-hoje' : '');
      ?>
      <div class="<?= $cls ?>">
        <span class="col-num"><?= $num ?></span>
        <span class="col-data"><?= $data->format('d/m/Y') ?></span>
        <span class="col-leit"><?= htmlspecialchars($leitura) ?></span>
        <span class="col-test">
          <span class="badge badge-<?= $nt?'nt':'at' ?>"><?= $nt?'N.T.':'A.T.' ?></span>
          <?php if ($lido): ?><span class="check">✓</span><?php endif; ?>
        </span>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($total_pag > 1): ?>
    <nav class="pagination">
      <?php if ($pagina > 1): ?>
        <button type="submit" form="formPlano" name="p" value="<?= $pagina-1 ?>" class="pg-btn">← Ant.</button>
      <?php endif; ?>
      <?php
      $ini = max(1,$pagina-2); $fim2 = min($total_pag,$pagina+2);
      if ($ini>1){ echo '<button type="submit" form="formPlano" name="p" value="1" class="pg-btn">1</button>'; if($ini>2) echo '<span class="pg-dots">…</span>'; }
      for($pp=$ini;$pp<=$fim2;$pp++) echo '<button type="submit" form="formPlano" name="p" value="'.$pp.'" class="pg-btn '.($pp===$pagina?'active':'').'">'.$pp.'</button>';
      if($fim2<$total_pag){ if($fim2<$total_pag-1) echo '<span class="pg-dots">…</span>'; echo '<button type="submit" form="formPlano" name="p" value="'.$total_pag.'" class="pg-btn">'.$total_pag.'</button>'; }
      ?>
      <?php if ($pagina < $total_pag): ?>
        <button type="submit" form="formPlano" name="p" value="<?= $pagina+1 ?>" class="pg-btn">Próx. →</button>
      <?php endif; ?>
    </nav>
    <?php endif; ?>

  </div>
</section>

<?php endif; ?>

<footer class="site-footer">
  <div class="container footer-container">
    <p class="footer-verse">✦ <em>Lâmpada para os meus pés é a tua palavra — Sl 119:105</em></p>
    <p class="footer-dev">Desenvolvido por: <?= DESENVOLVEDOR ?></p>

    <div class="social-links">
      <a href="https://www.instagram.com/israel_silvaaaa/" target="_blank" class="social-icon">
        <i class="fab fa-instagram"></i>
      </a>
      <a href="https://github.com/israelsilvaa/" target="_blank" class="social-icon">
        <i class="fab fa-github"></i>
      </a>
      <a href="https://www.linkedin.com/in/israel-silva-472b21214/" target="_blank" class="social-icon">
        <i class="fab fa-linkedin-in"></i>
      </a>
      <a href="sobre.php" class="social-icon" title="Sobre o projeto">
        <i class="fas fa-info-circle"></i>
      </a>
    </div>

    <span class="version"><?= VERSAO ?></span>
  </div>
</footer>

<script>
const mapa = <?= json_encode(array_map(fn($m)=>['livro'=>$m['livro'],'caps'=>$m['caps']], $MAPA)) ?>;

function atualizarCaps() {
  const idx = parseInt(document.getElementById('livro_idx').value);
  const total = mapa[idx].caps;
  const sel = document.getElementById('cap_livro');
  const cur = parseInt(sel.value) || 1;
  sel.innerHTML = '';
  for (let c = 1; c <= total; c++) {
    const o = document.createElement('option');
    o.value = c;
    o.textContent = 'Capítulo ' + c;
    if (c === Math.min(cur, total)) o.selected = true;
    sel.appendChild(o);
  }
}

function ajustar(d) {
  const inp = document.getElementById('caps_dia');
  inp.value = Math.max(1, Math.min(20, parseInt(inp.value) + d));
}
</script>
</body>
</html>
