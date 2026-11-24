# VANTROX

Loja virtual demonstrativa de moda urbana, desenvolvida em PHP, HTML, CSS e JavaScript para um projeto acadêmico.

## Executar localmente

1. Instale o XAMPP e inicie o Apache.
2. Coloque a pasta do projeto em `C:\xampp\htdocs\VANTROX`.
3. Acesse `http://localhost/VANTROX/` no navegador.
4. Crie uma conta pela opção **Entrar** para testar o cadastro e o fluxo de pedidos.

O projeto não precisa de banco de dados ou dependências adicionais. Os dados de contas e pedidos são gravados localmente em `data/`, que precisa ter permissão de escrita para o Apache.

## Funcionalidades

- Catálogo com busca e filtros por público.
- Escolha de cor e tamanho, carrinho e fluxo demonstrativo de checkout.
- Cadastro e login de clientes.
- Banner rotativo e formulário de contato demonstrativo.

## Importante

Este projeto é apenas uma demonstração acadêmica. O checkout não processa pagamentos reais e os dados de cartão não devem ser informados. O formulário de contato não envia mensagens.

Arquivos locais de contas e pedidos (`data/*.json`) são ignorados pelo Git para evitar publicar informações pessoais.
