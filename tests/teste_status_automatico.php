<?php
/**
 * Teste de Status Automático
 * Verifica se dias passados são marcados como "Lido"
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Teste Status Automático</h2>";

try {
    require_once __DIR__ . '/../modules/XlsxWriterV4_Fixed.php';

    $x = new XlsxWriterV4_Fixed();
    $x->addSheet('Teste Status Automático');

    $x->setColWidth('A', 7);
    $x->setColWidth('B', 13);
    $x->setColWidth('C', 30);
    $x->setColWidth('D', 8);

    // Título
    $x->writeRowMerged('Teste: Status Automático baseado na Data', 1, 4, 'title');
    $x->writeRowMerged('Dias passados devem vir como "Lido"', 1, 4, 'sub');
    $x->writeRowMerged('', 1, 4, 'sub');

    // Cabeçalho
    $x->writeRow([
        ['#', 'header'],
        ['Data', 'header'],
        ['Leitura', 'header'],
        ['Test.', 'header'],
    ]);

    $x->markDataStart();

    // Criar datas de teste: alguns dias no passado, hoje, e futuros
    $hoje = new DateTime();
    $hoje->setTime(0, 0, 0);

    $dadosTeste = [];

    // Adicionar 3 dias no passado
    for ($i = 3; $i >= 1; $i--) {
        $data = clone $hoje;
        $data->modify("-{$i} days");
        $dadosTeste[] = $data;
    }

    // Adicionar hoje
    $dadosTeste[] = clone $hoje;

    // Adicionar 3 dias no futuro
    for ($i = 1; $i <= 3; $i++) {
        $data = clone $hoje;
        $data->modify("+{$i} days");
        $dadosTeste[] = $data;
    }

    // Escrever dados
    $statusEsperado = [];
    $infoDatas = [];

    foreach ($dadosTeste as $i => $data) {
        $dc = clone $data;
        $dc->setTime(0, 0, 0);

        $estilo = $i % 2 === 0 ? 'zebra_even' : 'zebra_odd';
        $ehPassado = $dc < $hoje;
        $statusEsperado[] = $ehPassado ? 'Lido' : 'Não lido';

        $infoDatas[] = [
            'data' => $data->format('d/m/Y'),
            'eh_passado' => $ehPassado,
            'status' => $ehPassado ? 'Lido' : 'Não lido'
        ];

        $x->writeRow([
            [$i + 1, $estilo],
            [$data->format('d/m/Y'), $estilo],
            ['Leitura teste ' . ($i + 1), $estilo],
            ['A.T.', $estilo],
        ]);
    }

    $x->markDataEnd();

    echo "<p>✅ Dados escritos</p>";
    echo "<p>📅 Data de hoje: <strong>" . $hoje->format('d/m/Y') . "</strong></p>";

    // Adicionar Status com array
    $x->addStatusColumnFixed($statusEsperado);
    echo "<p>✅ Status adicionado com valores automáticos</p>";

    // Mostrar tabela do que foi gerado
    echo "<hr>";
    echo "<h3>📊 Status Esperado:</h3>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; background: #fff;'>";
    echo "<tr style='background: #f0f7ff;'><th>Dia</th><th>Data</th><th>Status (automático)</th><th>Motivo</th></tr>";

    foreach ($infoDatas as $i => $info) {
        $cor = $info['status'] === 'Lido' ? '#28a745' : '#6c757d';
        $motivo = $info['eh_passado'] ? 'Data no passado' : 'Data hoje ou futura';
        echo "<tr>";
        echo "<td>" . ($i + 1) . "</td>";
        echo "<td>" . htmlspecialchars($info['data']) . "</td>";
        echo "<td style='color: $cor; font-weight: bold;'>" . htmlspecialchars($info['status']) . "</td>";
        echo "<td>" . htmlspecialchars($motivo) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    $x->addTotalsRowFixed();
    echo "<p>✅ Totais adicionados</p>";

    $x->addPieChartFixed();
    echo "<p>✅ Gráfico adicionado</p>";

    $bytes = $x->getBytes();
    file_put_contents(__DIR__ . '/teste_status_automatico.xlsx', $bytes);

    echo "<hr>";
    echo "<p class='success'>✅ <strong>SUCESSO!</strong></p>";
    echo "<p><a href='teste_status_automatico.xlsx' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 1.2em;'>📥 Baixar teste_status_automatico.xlsx</a></p>";

    echo "<hr>";
    echo "<h3>🧪 Verificar no Excel:</h3>";
    echo "<ul>";
    echo "<li>✅ Dias passados devem vir marcados como <strong>Lido</strong></li>";
    echo "<li>✅ Hoje e dias futuros devem vir como <strong>Não lido</strong></li>";
    echo "<li>✅ Totais devem mostrar pelo menos 3 Lidos</li>";
    echo "<li>✅ Gráfico deve mostrar progresso com 3+ fatias lidas</li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "<h2>❌ Erro</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
