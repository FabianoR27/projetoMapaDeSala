<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Professor extends CI_Controller {
    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    1  - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    2  - Conteúdo passado nulo ou vazio
    3  - Conteúdo zerado
    4  - Conteúdo não inteiro
    5  - Conteúdo não é um texto
    12 - Na atualização, pelo menos um atributo deve ser passado
    13 - CPF já cadastrado no sistema
    99 - Parâmetros passados do front não correspondem ao método
    */

    // Atributos privados da classe
    private $codigo;
    private $nome;
    private $cpf;
    private $tipo;
    private $status;

    // --- Getters dos atributos ---
    
    public function getCodigo()
    {
        return $this->codigo;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function getCpf()
    {
        return $this->cpf;
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function getStatus()
    {
        return $this->status;
    }

    // --- Setters dos atributos ---

    public function setCodigo($codigoFront)
    {
        $this->codigo = $codigoFront;
    }

    public function setNome($nomeFront)
    {
        $this->nome = $nomeFront;
    }

    public function setCpf($cpfFront)
    {
        $this->cpf = $cpfFront;
    }

    public function setTipo($tipoFront)
    {
        $this->tipo = $tipoFront;
    }

    public function setStatus($statusFront)
    {
        $this->status = $statusFront;
    }

    // --- Métodos de Regra de Negócio ---

    /**
     * Insere um novo professor
     */
    public function inserir() {
        // Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "nome"    => '0',
                "cpf" => '0',
                "tipo" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                // Validar campos quanto ao tipo de dado e tamanho (Helper)
                $retornoNome    = validarDados($resultado->nome, 'string', true);
                $retornoCpf     = validarDados($resultado->cpf, 'string', true);
                $retornoTipo    = validarDados($resultado->tipo, 'string', true);

                if($retornoNome['codigoHelper'] != 1){
                    $erros[] = [
                        'codigo' => $retornoNome['codigoHelper'],
                        'campo'  => 'Nome',
                        'msg'    => $retornoNome['msg']
                    ];
                }

                if($retornoCpf['codigoHelper'] != 1){
                    $erros[] = [
                        'codigo' => $retornoCpf['codigoHelper'],
                        'campo'  => 'Cpf',
                        'msg'    => $retornoCpf['msg']
                    ];
                }

                if($retornoTipo['codigoHelper'] != 1){
                    $erros[] = [
                        'codigo' => $retornoTipo['codigoHelper'],
                        'campo'  => 'Tipo',
                        'msg'    => $retornoTipo['msg']
                    ];
                }

                // Se não houver erros de formatação básica, fazemos a checagem de duplicidade no banco
                if (empty($erros)) {
                    $this->load->model('M_professor');
                    
                    // Consulta se já existe algum professor com este CPF
                    $checkCpf = $this->M_professor->consultar(0, '', $resultado->cpf, '');
                    
                    // Verifica se a consulta retornou sucesso e se o array de dados não está vazio
                    if ($checkCpf['codigo'] == 1 && !empty($checkCpf['dados'])) {
                        $erros[] = [
                            'codigo' => 13,
                            'campo'  => 'Cpf',
                            'msg'    => 'O CPF informado já está cadastrado no sistema.'
                        ];
                    }
                }

                // Se continuar sem erros após a validação do CPF, prossegue com a inserção
                if (empty($erros)) {
                    $this->setNome($resultado->nome);
                    $this->setCpf($resultado->cpf);
                    $this->setTipo($resultado->tipo);

                    $resBanco = $this->M_professor->inserir(
                        $this->getNome(),
                        $this->getCpf(),
                        $this->getTipo()
                    );

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        // Captura erro do banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg'    => $resBanco['msg']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        // Monta retorno único
        if ($sucesso == true) {
            $retorno = [
                'sucesso' => $sucesso, 
                'codigo'  => $resBanco['codigo'],
                'msg'     => $resBanco['msg']
            ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        // Transforma o array em JSON
        echo json_encode($retorno);
    }

    /**
     * Consulta professores
     */
    public function consultar() {
        // Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo"  => '0',
                "nome"    => '0',
                "cpf" => '0',
                "tipo" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                // Validar campos quanto ao tipo de dado e tamanho (Helper)
                $retornoCodigo  = validarDadosConsulta($resultado->codigo, 'int');
                $retornoNome    = validarDadosConsulta($resultado->nome, 'string');
                $retornoCpf = validarDadosConsulta($resultado->cpf, 'string');
                $retornoTipo = validarDadosConsulta($resultado->tipo, 'string');

                if($retornoCodigo['codigoHelper'] != 0){
                    $erros[] = [
                        'codigo' => $retornoCodigo['codigoHelper'],
                        'campo'  => 'Codigo',
                        'msg'    => $retornoCodigo['msg']
                    ];
                }

                if($retornoNome['codigoHelper'] != 0){
                    $erros[] = [
                        'codigo' => $retornoNome['codigoHelper'],
                        'campo'  => 'Nome',
                        'msg'    => $retornoNome['msg']
                    ];
                }

                if($retornoCpf['codigoHelper'] != 0){
                    $erros[] = [
                        'codigo' => $retornoCpf['codigoHelper'],
                        'campo'  => 'CPF',
                        'msg'    => $retornoCpf['msg']
                    ];
                }

                if($retornoTipo['codigoHelper'] != 0){
                    $erros[] = [
                        'codigo' => $retornoTipo['codigoHelper'],
                        'campo'  => 'Tipo',
                        'msg'    => $retornoTipo['msg']
                    ];
                }

                // Se não encontrar erros
                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);
                    $this->setNome($resultado->nome);
                    $this->setCpf($resultado->cpf);
                    $this->setTipo($resultado->tipo);

                    $this->load->model('M_professor');
                    $resBanco = $this->M_professor->consultar(
                        $this->getCodigo(),
                        $this->getNome(),
                        $this->getCpf(),
                        $this->getTipo()
                    );

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        // Captura erro do banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg'    => $resBanco['msg']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        // Monta retorno único
        if ($sucesso == true) {
            $retorno = [
                'sucesso' => $sucesso, 
                'codigo'  => $resBanco['codigo'],
                'msg'     => $resBanco['msg'],
                'dados'   => $resBanco['dados']
            ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        // Transforma o array em JSON
        echo json_encode($retorno);
    }

    /**
     * Altera os dados de um professor existente
     */
    public function alterar() {
        // Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo"  => '0',
                "nome"    => '0',
                "cpf" => '0',
                "tipo" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                // Pelo menos um dos parâmetros precisam ter dados para acontecer a atualização
                if(trim($resultado->nome) == '' && trim($resultado->cpf) == '' && trim($resultado->tipo) == ''){
                    $erros[] = [
                        'codigo' => 12,
                        'msg'    => 'Pelo menos um parâmetro precisa ser passado para atualização'
                    ];
                } else {
                    // Validar campos quanto ao tipo de dado e tamanho (Helper)
                    $retornoCodigo  = validarDados($resultado->codigo, 'int', true);
                    $retornoNome    = validarDadosConsulta($resultado->nome, 'string');
                    $retornoCpf = validarDadosConsulta($resultado->cpf, 'string');
                    $retornoTipo = validarDadosConsulta($resultado->tipo, 'string');

                    if($retornoCodigo['codigoHelper'] != 1){
                        $erros[] = [
                            'codigo' => $retornoCodigo['codigoHelper'],
                            'campo'  => 'Codigo',
                            'msg'    => $retornoCodigo['msg']
                        ];
                    }

                    if($retornoNome['codigoHelper'] != 0){
                        $erros[] = [
                            'codigo' => $retornoNome['codigoHelper'],
                            'campo'  => 'Nome',
                            'msg'    => $retornoNome['msg']
                        ];
                    }

                    if($retornoCpf['codigoHelper'] != 0){
                        $erros[] = [
                            'codigo' => $retornoCpf['codigoHelper'],
                            'campo'  => 'CPF',
                            'msg'    => $retornoCpf['msg']
                        ];
                    }

                    if($retornoTipo['codigoHelper'] != 0){
                        $erros[] = [
                            'codigo' => $retornoTipo['codigoHelper'],
                            'campo'  => 'Tipo',
                            'msg'    => $retornoTipo['msg']
                        ];
                    }

                    // Se não encontrar erros
                    if (empty($erros)) {
                        $this->setCodigo($resultado->codigo);
                        $this->setNome($resultado->nome);
                        $this->setCpf($resultado->cpf);
                        $this->setTipo($resultado->tipo);

                        $this->load->model('M_professor');
                        $resBanco = $this->M_professor->alterar(
                            $this->getCodigo(),
                            $this->getNome(),
                            $this->getCpf(),
                            $this->getTipo()
                        );

                        if ($resBanco['codigo'] == 1) {
                            $sucesso = true;
                        } else {
                            // Captura erro do banco
                            $erros[] = [
                                'codigo' => $resBanco['codigo'],
                                'msg'    => $resBanco['msg']
                            ];
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        // Monta retorno único
        if ($sucesso == true) {
            $retorno = [
                'sucesso' => $sucesso, 
                'codigo'  => $resBanco['codigo'],
                'msg'     => $resBanco['msg']
            ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        // Transforma o array em JSON
        echo json_encode($retorno);
    }

    /**
     * Desativa um professor (Exclusão lógica)
     */
    public function desativar() {
        // Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                // Validar código quanto ao tipo de dado e tamanho (Helper)
                $retornoCodigo = validarDados($resultado->codigo, 'int', true);

                if($retornoCodigo['codigoHelper'] != 1){
                    $erros[] = [
                        'codigo' => $retornoCodigo['codigoHelper'],
                        'campo'  => 'Codigo',
                        'msg'    => $retornoCodigo['msg']
                    ];
                }

                // Se não encontrar erros
                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);

                    $this->load->model('M_professor');
                    $resBanco = $this->M_professor->desativar($this->getCodigo());

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        // Captura erro do banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg'    => $resBanco['msg']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        // Monta retorno único
        if ($sucesso == true) {
            $retorno = [
                'sucesso' => $sucesso, 
                'codigo'  => $resBanco['codigo'],
                'msg'     => $resBanco['msg']
            ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        // Transforma o array em JSON
        echo json_encode($retorno);
    }
}