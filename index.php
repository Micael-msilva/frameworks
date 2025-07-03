<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Formulário de Conexão</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<div class="container">

<form method="POST" action="creator.php">
    <?php

    include 'mensagens.php';
    if (isset($_GET['msg']) ){
        echo "<div id='mensagem'>" . ($mensagens[$_GET['msg']] ?? "Erro desconhecido") . "</div>";
    }
    ?>
    <h1>EasyMVC</h1><h2>Configuração</h2>

    <label for="servidor">Servidor:</label>
    <input type="text" id="servidor" name="servidor" required>

    <label for="usuario">Usuário:</label>
    <input type="text" id="usuario" name="usuario" required>

    <label for="senha">Senha:</label>
    <input type="password" id="senha" name="senha">

    <label for="banco">Banco de Dados:</label>
    <select id="banco" name="banco" required>
        <option value="">Selecione o banco</option>
    </select>
    <button type="button" id="carregarBancos">Carregar Bancos</button>

    <button type="submit">Enviar</button>
</form>
</div>
<script>
document.getElementById('carregarBancos').onclick = function() {
    const servidor = document.getElementById('servidor').value;
    const usuario = document.getElementById('usuario').value;
    const senha = document.getElementById('senha').value;
    fetch('lista_bancos.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `servidor=${encodeURIComponent(servidor)}&usuario=${encodeURIComponent(usuario)}&senha=${encodeURIComponent(senha)}`
    })
    .then(r => r.json())
    .then(bancos => {
        const select = document.getElementById('banco');
        select.innerHTML = '<option value="">Selecione o banco</option>';
        bancos.forEach(banco => {
            select.innerHTML += `<option value="${banco}">${banco}</option>`;
        });
    });
};
</script>
</body>
</html>
