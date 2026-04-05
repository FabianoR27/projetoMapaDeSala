<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sala extends CI_Controller {

    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    1 - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    2 - Conteúdo passado nulo ou vazio
    3 - Conteúdo zerado
    4 - Conteúdo não inteiro
    5 - Conteúdo não é um texto
    6 - Data em formato inválido
    7 - Hora em formato inválido
    12- Na atualização, pelo menos um atributo deve ser passado
    99 - Parâmetros passados do front não correspondem ao método
    */

    //Atributos privados da classe
    private $codigo;
    private $descricao;
    private $andar;
    private $capacidade;
    private $status;

    //Getters dos atributos
    public function getCodigo()
    {
        return $this->codigo;
    }

    public function getDescricao()
    {
        return $this->descricao;
    }

    public function getAndar()
    {
        return $this->andar;
    }

    public function getCapacidade()
    {
        return $this->capacidade;
    }

    public function getStatus()
    {
        return $this->status;
    }

    //Setters dos atributos
    public function setCodigo($codigoFront)
    {
        $this->codigo = $codigoFront;
    }

    public function setDescricao($descricaoFront)
    {
        $this->descricao = $descricaoFront;
    }

    public function setAndar($andarFront)
    {
        $this->andar = $andarFront;
    }

    public function setCapacidade($capacidadeFront)
    {
        $this->capacidade = $capacidadeFront;
    }

    public function setStatus($statusFront)
    {
        $this->status = $statusFront;
    }

    public function inserir()
    {
        //Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;
        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo" => '0',
                "descricao" => '0',
                "andar" => '0',
                "capacidade" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                // Validar campos quanto ao tipo de dado e tamanho (Helper)
                $retornoCodigo = validarDados($resultado->codigo, 'int', true);
                $retornoDescricao = validarDados($resultado->descricao, 'string', true);
                $retornoAndar = validarDados($resultado->andar, 'int', true);
                $retornoCapacidade = validarDados($resultado->capacidade, 'int', true);

                if ($retornoCodigo['codigoHelper'] != 1) {
                    $erros[] = ['codigo' => $retornoCodigo['codigoHelper'], 'campo' => 'Codigo', 'msg' => $retornoCodigo['msg']];
                }
                if ($retornoDescricao['codigoHelper'] != 1) {
                    $erros[] = ['codigo' => $retornoDescricao['codigoHelper'], 'campo' => 'Descrição', 'msg' => $retornoDescricao['msg']];
                }
                if ($retornoAndar['codigoHelper'] != 1) {
                    $erros[] = ['codigo' => $retornoAndar['codigoHelper'], 'campo' => 'Andar', 'msg' => $retornoAndar['msg']];
                }
                if ($retornoCapacidade['codigoHelper'] != 1) {
                    $erros[] = ['codigo' => $retornoCapacidade['codigoHelper'], 'campo' => 'Capacidade', 'msg' => $retornoCapacidade['msg']];
                }

                //Se não encontrar erros
                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);
                    $this->setDescricao($resultado->descricao);
                    $this->setAndar($resultado->andar);
                    $this->setCapacidade($resultado->capacidade);
                    $this->load->model('m_sala');
                    $resBanco = $this->m_sala->inserir(
                        $this->getCodigo(),
                        $this->getDescricao(),
                        $this->getAndar(),
                        $this->getCapacidade()
                    );

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        // Captura erro do banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg' => $resBanco['msg']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        // Monta retorno único
        if ($sucesso == true) {
            $retorno = ['sucesso' => $sucesso, 'msg' => 'Sala cadastrada corretamente.'];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        // Transforma o array em JSON
        echo json_encode($retorno);
    }


    public function consultar()
    {
        //Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {

            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo" => '0',
                "descricao" => '0',
                "andar" => '0',
                "capacidade" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                // Validar campos quanto ao tipo de dado e tamanho (Helper)
                $retornoCodigo = validarDadosConsulta($resultado->codigo, 'int');
                $retornoDescricao = validarDadosConsulta($resultado->descricao, 'string');
                $retornoAndar = validarDadosConsulta($resultado->andar, 'int');
                $retornoCapacidade = validarDadosConsulta($resultado->capacidade, 'int');

                if ($retornoCodigo['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoCodigo['codigoHelper'],
                        'campo' => 'Codigo',
                        'msg' => $retornoCodigo['msg']
                    ];
                }

                if ($retornoDescricao['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoDescricao['codigoHelper'],
                        'campo' => 'Descrição',
                        'msg' => $retornoDescricao['msg']
                    ];
                }

                if ($retornoAndar['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoAndar['codigoHelper'],
                        'campo' => 'Andar',
                        'msg' => $retornoAndar['msg']
                    ];
                }

                if ($retornoCapacidade['codigoHelper'] != 0) {
                    $erros[] = [
                        'codigo' => $retornoCapacidade['codigoHelper'],
                        'campo' => 'Capacidade',
                        'msg' => $retornoCapacidade['msg']
                    ];
                }

                //Se não encontrar erros
                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);
                    $this->setDescricao($resultado->descricao);
                    $this->setAndar($resultado->andar);
                    $this->setCapacidade($resultado->capacidade);

                    $this->load->model('m_sala');
                    $resBanco = $this->m_sala->consultar(
                        $this->getCodigo(),
                        $this->getDescricao(),
                        $this->getAndar(),
                        $this->getCapacidade()
                    );

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        // Captura erro do banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg' => $resBanco['msg']
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
                'codigo' => $resBanco['codigo'],
                'msg' => $resBanco['msg'],
                'dados' => $resBanco['dados']
            ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        // Transforma o array em JSON
        echo json_encode($retorno);
    }

    // método para alterar uma sala
    public function alterar () {
        // atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo" => '0',
                "descricao" => '0',
                "andar" => '0',
                "capacidade" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // validar vindos de forma correta do frontend (helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                // pelo menos um dos três parâmetros precisa ser passado para acontecer a atualização
                if (trim($resultado -> descricao) == '' && trim($resultado -> andar) == '' && trim($resultado -> capacidade) == '') {
                    $erros[] = ['codigo' => 12, 'msg' => 'Pelo menos um campo precisa ser informado para atualização.'];
                } else {
                    // validar campos quanto ao tipo de dado e tamanho (helper)
                    $retornoCodigo = validarDados($resultado -> codigo, 'int', true);
                    $retornoDescricao = validarDadosConsulta($resultado -> descricao, 'string');
                    $retornoAndar = validarDadosConsulta($resultado -> andar, 'int');
                    $retornoCapacidade = validarDadosConsulta($resultado -> capacidade, 'int');

                    if ($retornoCodigo['codigoHelper'] != 1) {
                        $erros[] = ['codigo' => $retornoCodigo['codigoHelper'], 'campo' => 'Código', 'msg' => $retornoCodigo['msg']];
                    }

                    if ($retornoDescricao['codigoHelper'] != 0) {
                        $erros[] = ['codigo' => $retornoDescricao['codigoHelper'], 'campo' => 'Descrição', 'msg' => $retornoDescricao['msg']];
                    }

                    if ($retornoAndar['codigoHelper'] != 0) {
                        $erros[] = ['codigo' => $retornoAndar['codigoHelper'], 'campo' => 'Andar', 'msg' => $retornoAndar['msg']];
                    }

                    if ($retornoCapacidade['codigoHelper'] != 0) {
                        $erros[] = ['codigo' => $retornoCapacidade['codigoHelper'], 'campo' => 'Capacidade', 'msg' => $retornoCapacidade['msg']];
                    }

                    // se não encontrar erros
                    if (empty($erros)) {
                        $this -> setCodigo($resultado -> codigo);
                        $this -> setDescricao($resultado -> descricao);
                        $this -> setAndar($resultado -> andar);
                        $this -> setCapacidade($resultado -> capacidade);

                        $this -> load -> model('m_sala');
                        $resBanco = $this -> m_sala -> alterar(
                            $this -> getCodigo(),
                            $this -> getDescricao(),
                            $this -> getAndar(),
                            $this -> getCapacidade()
                        );

                        if ($resBanco['codigo'] == 1) {
                            $sucesso = true;
                        } else {
                            // captura erro do banco
                            $erros[] = ['codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
                        }
                    }
                }
            }
        }
        catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        // motna retorno único
        if ($sucesso == true) {
            $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        // transforma o array em json
        echo json_encode($retorno);
    }

    // método para desativar uma sala
    public function desativar () {
        // atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // validar vindos de forma correta do frontend (helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                // validar campos quanto ao tipo de dado e tamanho (helper)
                $retornoCodigo = validarDados($resultado -> codigo, 'int', true);

                if ($retornoCodigo['codigoHelper'] != 1) {
                    $erros[] = ['codigo' => $retornoCodigo['codigoHelper'], 'campo' => 'Codigo', 'msg' => $retornoCodigo['msg']];
                }

                // se não encontrar erros
                if (empty($erros)) {
                    $this -> setCodigo($resultado -> codigo);

                    $this -> load -> model('m_sala');
                    $resBanco = $this -> m_sala -> desativar($this -> getCodigo());

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        // captura erro do banco
                        $erros[] = ['codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
                    }
                }
            }
        }
        catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        // monta retorno único
        if ($sucesso == true) {
            $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        // transforma o array em json
        echo json_encode($retorno);
    }
}