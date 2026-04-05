<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Horario extends CI_Controller
{

    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    1 - Operação realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    2 - Conteúdo passado nulo ou vazio
    3 - Conteúdo zerado
    4 - Conteúdo não inteiro
    5 - Conteúdo não é um texto
    6 - Data em formato inválido
    7 - Hora em formato inválido
    12 - Na atualização, pelo menos um atributo deve ser passado
    13 - Hora inicial é maior que a hora final
    14 - Data inicial é maior que a data final
    99 - Parâmetros passados do front não correspondem ao método
    */

    //Atributos privados da classe
    private $codigo;
    private $descricao;
    private $horaInicial;
    private $horaFinal;
    private $status;

    // Getters dos atributos da classe
    public function getCodigo()
    {
        return $this->codigo;
    }

    public function getDescricao()
    {
        return $this->descricao;
    }

    public function getHoraInicial()
    {
        return $this->horaInicial;
    }

    public function getHoraFinal()
    {
        return $this->horaFinal;
    }

    public function getStatus()
    {
        return $this->status;
    }

    // Setters dos atributos da classe
    public function setCodigo($codigoFront)
    {
        $this->codigo = $codigoFront;
    }

    public function setDescricao($descricaoFront)
    {
        $this->descricao = $descricaoFront;
    }

    public function setHoraInicial($horaInicialFront)
    {
        $this->horaInicial = $horaInicialFront;
    }

    public function setHoraFinal($horaFinalFront)
    {
        $this->horaFinal = $horaFinalFront;
    }

    public function setStatus($statusFront)
    {
        $this->status = $statusFront;
    }

    public function inserir()
    {
        // ATRIBUTOS PARA CONTROLAR O STATUS DE NOSSO MÉTODO
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                'descricao'   => 0,
                'horaInicial' => 0,
                'horaFinal'   => 0
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // validar os dados vindo do front-end (helper)
                $erros = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no Front-end.'];
            } else {
                // validar campos quanto ao tipo de dado e tamanho (Helper)
                $retornoDescricao = validarDados($resultado->descricao, 'string', true); // o campo descrição é obrigatório para a inserção, por isso o true
                $retornoHoraInicial = validarDados($resultado->horaInicial, 'hora', true);
                $retornoHoraFinal = validarDados($resultado->horaFinal, 'hora', true);
                $retornoComparacaoHora = compararDataHora($resultado->horaInicial, $resultado->horaFinal, 'hora');

                if ($retornoDescricao['codigoHelper'] != 1) {
                    $erros[] = [
                        'codigo' => $retornoDescricao['codigoHelper'],
                        'campo' => 'Descrição',
                        'msg' => $retornoDescricao['msg']
                    ];
                }

                if ($retornoHoraInicial['codigoHelper'] != 1) {
                    $erros[] = [
                        'codigo' => $retornoHoraInicial['codigoHelper'],
                        'campo' => 'Hora Inicial',
                        'msg' => $retornoHoraInicial['msg']
                    ];
                }

                if ($retornoHoraFinal['codigoHelper'] != 1) {
                    $erros[] = [
                        'codigo' => $retornoHoraFinal['codigoHelper'],
                        'campo' => 'Hora Final',
                        'msg' => $retornoHoraFinal['msg']
                    ];
                }

                // validar se a hora inicial é menor que a hora final
                if ($retornoComparacaoHora['codigoHelper'] != 1) {
                    $erros[] = [
                        'codigo' => $retornoComparacaoHora['codigoHelper'],
                        'campo' => 'Hora inicial e Hora final',
                        'msg' => $retornoComparacaoHora['msg']
                    ];
                }

                // se não tiver erros, pode inserir no banco de dados
                if (empty($erros)) {
                    $this->setDescricao($resultado->descricao);
                    $this->setHoraInicial($resultado->horaInicial);
                    $this->setHoraFinal($resultado->horaFinal);

                    $this->load->model('m_horario');
                    $resBanco = $this->m_horario->inserir($this->descricao, $this->horaInicial, $this->horaFinal);

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        // captura o erro do banco de dados e retorna para o front-end
                        $erros[] = ['codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = 'Erro inesperado: ' . $e->getMessage();
        }

        // RETORNO PARA O FRONT-END
        if ($sucesso) {
            $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        // transforma o array em JSON e retorna para o front-end
        echo json_encode($retorno);
    }

    // método para consultar
    public function consultar() {
        $erros = [];
        $sucesso = false;
    
        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
    
            // 1. Defina o que é OBRIGATÓRIO. 
            // Se a consulta pode ser feita só com código, a lista de verificação deve ser flexível.
            if (!isset($resultado->codigo) && !isset($resultado->descricao)) {
                $erros[] = ['codigo' => 99, 'msg' => 'Informe ao menos um critério de busca (Código ou Descrição).'];
            } else {
                
                // --- VALIDAÇÃO DO CÓDIGO ---
                // Só valida se o código foi enviado e não está vazio
                if (!empty($resultado->codigo)) {
                    $retornoCodigo = validarDados($resultado->codigo, 'int', false);
                    if ($retornoCodigo['codigoHelper'] != 1) {
                        $erros[] = ['codigo' => $retornoCodigo['codigoHelper'], 'campo' => 'Código', 'msg' => $retornoCodigo['msg']];
                    }
                }
    
                // --- VALIDAÇÃO DA DESCRIÇÃO ---
                if (!empty($resultado->descricao)) {
                    $retornoDescricao = validarDados($resultado->descricao, 'string', false);
                    if ($retornoDescricao['codigoHelper'] != 1) { // Ajustado para != 1 (sucesso)
                        $erros[] = ['codigo' => $retornoDescricao['codigoHelper'], 'campo' => 'Descrição', 'msg' => $retornoDescricao['msg']];
                    }
                }
    
                // --- VALIDAÇÃO DE HORAS (Só se ambas forem preenchidas) ---
                if (!empty($resultado->horaInicial) && !empty($resultado->horaFinal)) {
                    $retornoHoraInicial = validarDados($resultado->horaInicial, 'hora', false);
                    $retornoHoraFinal = validarDados($resultado->horaFinal, 'hora', false);
                    $retornoComparacao = compararDataHora($resultado->horaInicial, $resultado->horaFinal, 'hora');
    
                    if ($retornoComparacao['codigoHelper'] != 1) {
                        $erros[] = ['codigo' => $retornoComparacao['codigoHelper'], 'campo' => 'Horário', 'msg' => $retornoComparacao['msg']];
                    }
                }
    
                // Se não houver erros de validação, prossegue
                if (empty($erros)) {
                    // Prepara os dados (se não existir no JSON, envia null ou vazio para o Model)
                    $codigo    = $resultado->codigo ?? null;
                    $descricao = $resultado->descricao ?? null;
                    $horaIni   = $resultado->horaInicial ?? null;
                    $horaFim   = $resultado->horaFinal ?? null;
    
                    $this->load->model('m_horario');
                    $resBanco = $this->m_horario->consultar($codigo, $descricao, $horaIni, $horaFim);
    
                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                        $dadosConsulta = $resBanco['dados'];
                    } else {
                        $erros[] = ['codigo' => $resBanco['codigo'], 'msg' => $resBanco['msg']];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 500, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }
    
        // RETORNO PARA O FRONT-END
        $retorno = $sucesso 
            ? ['sucesso' => true, 'codigo' => 1, 'msg' => 'Consulta realizada.', 'dados' => $dadosConsulta]
            : ['sucesso' => false, 'erros' => $erros];
    
        echo json_encode($retorno);
    }


    // método para atualizar
    public function alterar() {
        //Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {

            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo" => '0',
                "descricao" => '0',
                "horaInicial" => '0',
                "horaFinal" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                //Pelo menos um dos três parâmetros precisam ter dados para acontecer a atualização
                if (trim($resultado->descricao) == '' && trim($resultado->horaInicial) == '' && trim($resultado->horaFinal) == '') {
                    $erros[] = [
                        'codigo' => 12,
                        'msg' => 'Pelo menos um parâmetro precisa ser passado para atualização'
                    ];
                } else {

                    // Validar campos quanto ao tipo de dado e tamanho (Helper)
                    $retornoCodigo = validarDados($resultado->codigo, 'int', true);
                    $retornoDescricao = validarDadosConsulta($resultado->descricao, 'string');
                    $retornoHoraInicial = validarDadosConsulta($resultado->horaInicial, 'hora');
                    $retornoHoraFinal = validarDadosConsulta($resultado->horaFinal, 'hora');
                    $retornoComparacaoHoras = compararDataHora($resultado->horaInicial, $resultado->horaFinal, 'hora');

                    if ($retornoCodigo['codigoHelper'] != 1) {
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

                    if ($retornoHoraInicial['codigoHelper'] != 0) {
                        $erros[] = [
                            'codigo' => $retornoHoraInicial['codigoHelper'],
                            'campo' => 'Hora Inicial',
                            'msg' => $retornoHoraInicial['msg']
                        ];
                    }

                    if ($retornoHoraFinal['codigoHelper'] != 0) {
                        $erros[] = [
                            'codigo' => $retornoHoraFinal['codigoHelper'],
                            'campo' => 'Hora Final',
                            'msg' => $retornoHoraFinal['msg']
                        ];
                    }

                    // Validar se a hora inicial é maior que a hora final
                    if ($retornoComparacaoHoras['codigoHelper'] != 1) {
                        $erros[] = [
                            'codigo' => $retornoComparacaoHoras['codigoHelper'],
                            'campo' => 'Hora Inicial e Hora Final',
                            'msg' => $retornoComparacaoHoras['msg']
                        ];
                    }

                    //Se não encontrar erros
                    if (empty($erros)) {
                        $this->setCodigo($resultado->codigo);
                        $this->setDescricao($resultado->descricao);
                        $this->setHoraInicial($resultado->horaInicial);
                        $this->setHoraFinal($resultado->horaFinal);

                        $this->load->model('m_horario');
                        $resBanco = $this->m_horario->alterar(
                            $this->getCodigo(),
                            $this->getDescricao(),
                            $this->getHoraInicial(),
                            $this->getHoraFinal()
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
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        // Monta retorno único
        if ($sucesso == true) {
            $retorno = [
                'sucesso' => $sucesso,
                'codigo' => $resBanco['codigo'],
                'msg' => $resBanco['msg']
            ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        // Retorna para o front-end
        echo json_encode($retorno);
    }

    // método para desativar
    public function desativar() {
        //Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                // Validar dados vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                // Validar campos quanto ao tipo de dado e tamanho (Helper)
                $retornoCodigo = validarDados($resultado->codigo, 'int', true);

                if ($retornoCodigo['codigoHelper'] != 1) {
                    $erros[] = [
                        'codigo' => $retornoCodigo['codigoHelper'],
                        'campo' => 'Codigo',
                        'msg' => $retornoCodigo['msg']
                    ];
                }

                //Se não encontrar erros
                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);

                    $this->load->model('m_horario');
                    $resBanco = $this->m_horario->desativar(
                        $this->getCodigo()
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

        // monta retorno único
        if ($sucesso == true) {
            $retorno = [
                'sucesso' => $sucesso,
                'codigo' => $resBanco['codigo'],
                'msg' => $resBanco['msg']
            ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        // retorna para o front-end
        echo json_encode($retorno);
    }
}
