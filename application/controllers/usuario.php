<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
    Validação dos tipos de retornos nas validações (Código de erro)
    1  - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    2  - Conteúdo passado nulo ou vazio
    3  - Conteúdo zerado
    4  - Conteúdo não inteiro
    5  - Conteúdo não é um texto
    12 - Na atualização, pelo menos um atributo deve ser passado
    99 - Parâmetros passados do front não correspondem ao método
    */

class Usuario extends CI_Controller
{

    // Atributos privados da classe
    private $codigo;
    private $nome;
    private $email;
    private $usuario;
    private $senha;
    private $status;

    // --- Getters ---
    public function getCodigo()
    {
        return $this->codigo;
    }
    public function getNome()
    {
        return $this->nome;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getUsuario()
    {
        return $this->usuario;
    }
    public function getSenha()
    {
        return $this->senha;
    }
    public function getStatus()
    {
        return $this->status;
    }

    // --- Setters ---
    public function setCodigo($codigoFront)
    {
        $this->codigo = $codigoFront;
    }
    public function setNome($nomeFront)
    {
        $this->nome = $nomeFront;
    }
    public function setEmail($emailFront)
    {
        $this->email = $emailFront;
    }
    public function setUsuario($usuarioFront)
    {
        $this->usuario = $usuarioFront;
    }
    public function setSenha($senhaFront)
    {
        $this->senha = $senhaFront;
    }
    public function setStatus($statusFront)
    {
        $this->status = $statusFront;
    }

    // --- Métodos de Regra de Negócio ---

    public function inserir()
    {
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            // Padronizado de acordo com o JSON e Model
            $lista = [
                "nome"    => '0',
                "email"   => '0',
                "usuario" => '0',
                "senha"   => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                $retornoNome    = validarDados($resultado->nome, 'string', true);
                $retornoEmail   = validarDados($resultado->email, 'string', true);
                $retornoUsuario = validarDados($resultado->usuario, 'string', true);
                $retornoSenha   = validarDados($resultado->senha, 'string', true);

                if ($retornoNome['codigoHelper'] != 1) {
                    $erros[] = ['codigo' => $retornoNome['codigoHelper'], 'campo'  => 'Nome', 'msg' => $retornoNome['msg']];
                }
                if ($retornoEmail['codigoHelper'] != 1) {
                    $erros[] = ['codigo' => $retornoEmail['codigoHelper'], 'campo'  => 'Email', 'msg' => $retornoEmail['msg']];
                }
                if ($retornoUsuario['codigoHelper'] != 1) {
                    $erros[] = ['codigo' => $retornoUsuario['codigoHelper'], 'campo'  => 'Usuario', 'msg' => $retornoUsuario['msg']];
                }
                if ($retornoSenha['codigoHelper'] != 1) {
                    $erros[] = ['codigo' => $retornoSenha['codigoHelper'], 'campo'  => 'Senha', 'msg' => $retornoSenha['msg']];
                }

                if (empty($erros)) {
                    $this->setNome($resultado->nome);
                    $this->setEmail($resultado->email);
                    $this->setUsuario($resultado->usuario);
                    $this->setSenha($resultado->senha);

                    $this->load->model('m_usuario');
                    $resBanco = $this->m_usuario->inserir(
                        $this->getNome(),
                        $this->getEmail(),
                        $this->getUsuario(),
                        $this->getSenha()
                    );

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        $erros[] = ['codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        if ($sucesso) {
            $retorno = ['sucesso' => $sucesso, 'codigo'  => $resBanco['codigo'], 'msg' => $resBanco['msg']];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }
        echo json_encode($retorno);
    }

    public function consultar()
    {
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            $lista = [
                "codigo"  => '0',
                "nome"    => '0',
                "email"   => '0',
                "usuario" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                $retornoCodigo  = validarDadosConsulta($resultado->codigo, 'int');
                $retornoNome    = validarDadosConsulta($resultado->nome, 'string');
                $retornoEmail   = validarDadosConsulta($resultado->email, 'string');
                $retornoUsuario = validarDadosConsulta($resultado->usuario, 'string');

                if ($retornoCodigo['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoCodigo['codigoHelper'], 'campo' => 'Codigo', 'msg' => $retornoCodigo['msg']];
                }
                if ($retornoNome['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoNome['codigoHelper'], 'campo' => 'Nome', 'msg' => $retornoNome['msg']];
                }
                if ($retornoEmail['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoEmail['codigoHelper'], 'campo' => 'Email', 'msg' => $retornoEmail['msg']];
                }
                if ($retornoUsuario['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoUsuario['codigoHelper'], 'campo' => 'Usuario', 'msg' => $retornoUsuario['msg']];
                }

                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);
                    $this->setNome($resultado->nome);
                    $this->setEmail($resultado->email);
                    $this->setUsuario($resultado->usuario);

                    $this->load->model('m_usuario');
                    $resBanco = $this->m_usuario->consultar(
                        $this->getCodigo(),
                        $this->getNome(),
                        $this->getEmail(),
                        $this->getUsuario()
                    );

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        $erros[] = ['codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        if ($sucesso) {
            $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg'], 'dados' => $resBanco['dados']];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }
        echo json_encode($retorno);
    }

    public function alterar()
    {
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            $lista = [
                "codigo"  => '0',
                "nome"    => '0',
                "email"   => '0',
                "usuario" => '0',
                "senha"   => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                if (trim($resultado->nome) == '' && trim($resultado->email) == '' && trim($resultado->usuario) == '' && trim($resultado->senha) == '') {
                    $erros[] = ['codigo' => 12, 'msg' => 'Pelo menos um parâmetro precisa ser passado para atualização'];
                } else {
                    $retornoCodigo  = validarDados($resultado->codigo, 'int', true);
                    $retornoNome    = validarDadosConsulta($resultado->nome, 'string');
                    $retornoEmail   = validarDadosConsulta($resultado->email, 'string');
                    $retornoUsuario = validarDadosConsulta($resultado->usuario, 'string');
                    $retornoSenha   = validarDadosConsulta($resultado->senha, 'string');

                    if ($retornoCodigo['codigoHelper'] != 1) {
                        $erros[] = ['codigo' => $retornoCodigo['codigoHelper'], 'campo' => 'Codigo', 'msg' => $retornoCodigo['msg']];
                    }
                    if ($retornoNome['codigoHelper'] != 0) {
                        $erros[] = ['codigo' => $retornoNome['codigoHelper'], 'campo' => 'Nome', 'msg' => $retornoNome['msg']];
                    }
                    if ($retornoEmail['codigoHelper'] != 0) {
                        $erros[] = ['codigo' => $retornoEmail['codigoHelper'], 'campo' => 'Email', 'msg' => $retornoEmail['msg']];
                    }
                    if ($retornoUsuario['codigoHelper'] != 0) {
                        $erros[] = ['codigo' => $retornoUsuario['codigoHelper'], 'campo' => 'Usuario', 'msg' => $retornoUsuario['msg']];
                    }
                    if ($retornoSenha['codigoHelper'] != 0) {
                        $erros[] = ['codigo' => $retornoSenha['codigoHelper'], 'campo' => 'Senha', 'msg' => $retornoSenha['msg']];
                    }

                    if (empty($erros)) {
                        $this->setCodigo($resultado->codigo);
                        $this->setNome($resultado->nome);
                        $this->setEmail($resultado->email);
                        $this->setUsuario($resultado->usuario);
                        $this->setSenha($resultado->senha);

                        $this->load->model('m_usuario');
                        $resBanco = $this->m_usuario->alterar(
                            $this->getCodigo(),
                            $this->getNome(),
                            $this->getEmail(),
                            $this->getUsuario(),
                            $this->getSenha()
                        );

                        if ($resBanco['codigo'] == 1) {
                            $sucesso = true;
                        } else {
                            $erros[] = ['codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        if ($sucesso) {
            $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }
        echo json_encode($retorno);
    }

    public function desativar()
    {
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = ["codigo" => '0'];

            if (verificarParam($resultado, $lista) != 1) {
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                $retornoCodigo = validarDados($resultado->codigo, 'int', true);

                if ($retornoCodigo['codigoHelper'] != 1) {
                    $erros[] = ['codigo' => $retornoCodigo['codigoHelper'], 'campo' => 'Codigo', 'msg' => $retornoCodigo['msg']];
                }

                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);

                    $this->load->model('m_usuario');
                    $resBanco = $this->m_usuario->desativar($this->getCodigo());

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        $erros[] = ['codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        if ($sucesso) {
            $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }
        echo json_encode($retorno);
    }

    /**
     * Realiza a autenticação (Login) do usuário
     */
    /**
     * Realiza a autenticação (Login) do usuário
     */
    public function login() {
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            
            $lista = [
                "usuario" => '0',
                "senha"   => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                $retornoUsuario = validarDados($resultado->usuario, 'string', true);
                $retornoSenha   = validarDados($resultado->senha, 'string', true);

                if ($retornoUsuario['codigoHelper'] != 1) {
                    $erros[] = [
                        'codigo' => $retornoUsuario['codigoHelper'],
                        'campo'  => 'Usuario',
                        'msg'    => $retornoUsuario['msg']
                    ];
                }

                if ($retornoSenha['codigoHelper'] != 1) {
                    $erros[] = [
                        'codigo' => $retornoSenha['codigoHelper'],
                        'campo'  => 'Senha',
                        'msg'    => $retornoSenha['msg']
                    ];
                }

                if (empty($erros)) {
                    $this->setUsuario($resultado->usuario);
                    $this->setSenha($resultado->senha);

                    $this->load->model('m_usuario');
                    $resBanco = $this->m_usuario->validaLogin(
                        $this->getUsuario(),
                        $this->getSenha()
                    );

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
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

        // Monta o retorno simplificado conforme solicitado
        if ($sucesso == true) {
            $retorno = [
                'sucesso' => true, 
                'codigo'  => 1,
                'msg'     => 'login realizado com sucesso' // Mensagem limpa e sem o nó "dados"
            ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        echo json_encode($retorno);
    }
}
