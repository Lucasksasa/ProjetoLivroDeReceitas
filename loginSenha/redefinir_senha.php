<?php
require_once '../BancoDeDados/conexao.php';

if (!isset($_GET['token'])) {
    die("Token inválido!");
}

$token = $_GET['token'];

// Verifica se o token existe e está válido
$sql = "SELECT * FROM recuperacao_senha WHERE token = :token AND expiracao >= NOW()";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":token", $token);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    die("Link expirado ou inválido.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nova_senha = $_POST['nova_senha'];
    $confirmar = $_POST['confirmar'];

    if ($nova_senha !== $confirmar) {
        echo "<script>alert('Senhas não coincidem!');</script>";
    } else {
        // Atualiza a senha no banco (procura pelo e-mail do token)
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        $email = $dados['email'];

        $update = "UPDATE logins SET senha = :senha WHERE email = :email";
        $stmtUpdate = $conn->prepare($update);
        $stmtUpdate->bindParam(":senha", $nova_senha); // Idealmente, use hash
        $stmtUpdate->bindParam(":email", $email);
        $stmtUpdate->execute();

        // Invalida o token
        $delete = "DELETE FROM recuperacao_senha WHERE token = :token";
        $stmtDelete = $conn->prepare($delete);
        $stmtDelete->bindParam(":token", $token);
        $stmtDelete->execute();

        echo "<script>alert('Senha atualizada com sucesso!'); window.location.href='login.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="../styles/redefinir.css">
</head>
<body>
    <h2>Redefinir Senha</h2>
    <form method="POST">
        <input type="password" name="nova_senha" placeholder="Nova senha" required>
        <input type="password" name="confirmar" placeholder="Confirme a nova senha" required>
        <button type="submit">Redefinir</button>
    </form>
</body>
</html>
