<?php include '../projetoMapaDeSala/assets/includes/header.php'; ?>

<body>
    <div class="container">
        <!-- Logo do formulário -->
        <div class="text-center">
            <img src="../projetoMapaDeSala/assets/img/logo_fatecSR.png" alt="Logo do Sistema de Mapa de Sala" class="logo" style="max-width: 200px; margin-bottom: 20px;">
        </div>

        <div class="panel-body">
            <form id="login" autocomplete="off">
                <fieldset>
                    <!-- Campo de usuário -->
                    <div class="form-group">
                        <input id="txtUsuario" class="form-control" placeholder="Usuário" name="txtUsuario" type="text" autofocus required>
                    </div>

                    <!-- Campo de senha com ícone para mostrar/ocultar a senha -->
                    <div class="form-group">
                        <div class="input-group">

                            <input id="txtSenha" type="password" class="form-control" placeholder="Senha" name="txtSenha" required>
                            <div class="input-group-append">

                                <!-- TALVEZ TENHA QUE MUDAR AQUI -->
                                <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                                    <i class="fa-solid fa-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Botão de login -->
                    <button id="btnEntrar" class="btn btn-block btnAcao" onclick="validarLogin()">Entrar</button>
                </fieldset>
            </form>
        </div>
    </div>
    
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="../projetoMapaDeSala/assets/js/sweetalert2.all.min.js"></script>
</html>

<script>
    // Função para validar o login
    async function validarLogin() {
        event.preventDefault(); // Evita o envio do formulário

        const usuario = document.getElementById('txtUsuario').value;
        const senha = document.getElementById('txtSenha').value;

        try {
            // Função para obter a URL base do CodeIgniter
            const base_url = function(url = '') {
                return "<?= base_url() ?>" + url;
            }

            // Envia os dados de login para o servidor usando Fetch API
            const response = await fetch ('Usuario/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    usuario: usuario,
                    senha: senha
                })
            });

            const result = await response.json();

            if (result.codigo == 1) {
                // Login bem-sucedido
                Swal.fire({
                    icon: 'success',
                    title: 'Login bem-sucedido!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = base_url('Funcoes/indexPagina'); // Redireciona para o dashboard
                });
            } else {
                // Login falhou
                // 1. MApeia e junta as mensagens de erro em uma tag
                const mensagensErro = result.erros.map(erro => `<li> ${erro.msg}</li>`).join('');
                const mensagemCompleta = `<ul class="list-group"> ${mensagensErro} </ul>`;
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de login',
                    html: mensagemCompleta,
                    showConfirmButton: true // Exibe o botão de confirmação para o usuário ler os erros
                });
            }
        } catch (error) {
            console.error('Erro ao validar login:', error);
        }
    }

    // Função para mostrar/ocultar a senha
    document.getElementById('togglePassword').addEventListener('click', function () {
        // Obtém o campo de senha e alterna seu tipo entre 'password' e 'text'
        const senhaInput = document.getElementById('txtSenha');
        const tipo = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
        senhaInput.setAttribute('type', tipo);

        // Alterna o ícone entre olho aberto e olho fechado
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
</script>