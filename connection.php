<?php

class Connection
{
    private $pdo;

    public function __construct()
    {
        try {
            // O caminho para o banco de dados. __DIR__ garante que o caminho seja sempre relativo ao arquivo atual.
            $databasePath = __DIR__ . '/database/db.sqlite';
            
            // Cria a conexão PDO
            $this->pdo = new PDO('sqlite:' . $databasePath);
            
            // Configura o PDO para lançar exceções em caso de erro
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            // Se a conexão falhar, exibe o erro e encerra o script
            die("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }

    /**
     * Retorna a instância do objeto PDO para ser usada em queries preparadas.
     * Esta é a função que estava faltando.
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Executa uma query simples. Mantemos esta função para o index.php funcionar.
     */
    public function query($sql)
    {
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}