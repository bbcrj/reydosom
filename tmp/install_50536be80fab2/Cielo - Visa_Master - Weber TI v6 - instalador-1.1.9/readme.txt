Passos para instalar o módulo da Cielo Visa/Master Virtuemart Webgenium:

1 - Copie os arquivos

2 - Crie um novo pagamento

Nome:                Cartão de crédito - Visa/Mastercard
Codigo:              PGV
Classe de pagamento: ps_pagamento_visa.php

[x] Baseado em Formulário HTML

3 - Cole o texto do arquivo payment_code.txt em informações extras do pagamento:

payment_code.txt


4 - Configure: 
MODO DE TESTE	 	       - modo de teste ou não do servidor
Afiliação CIELO (TESTE)	   - 1001734898
Chave de Acesso (TESTE)	   - e84827130b9837473681c2787007da5914d6359947015a5cdb2b8843db0fa832
Url Retorno (TESTE)	 	   - URL/administrator/components/com_virtuemart/classes/payment/pagamento_visa/retorno_visa.php
Afiliação CIELO (PRODUÇÃO) - 
Chave de Acesso (PRODUÇÃO) - 	
Url Retorno (PRODUÇÃO)	   - 
Valor Mínimo	 		   - 0.01
Máx. Parcelas	 		   - 10
Tipo Parcelamento Juros	   - Emissor ou Cliente pagam os juros
Tipo da Autorização		   - Prefira a opção 2
Capturar Transação ou não  - Capturar automaticamente a transação ou não

------------------------------------------------------------------------------------------------------------------------

Steps to install the module Cielo Visa / Master Virtuemart Webgenium:

1 - Copy files

2 - Create a new payment

Name: Credit card - Visa / Mastercard
Code: PGV
Class of payment: ps_pagamento_visa.php

[x] Based on HTML Form

3 - Paste the text in the file information payment_code.txt extra payment:

payment_code.txt


4 - Set:
TEST MODE - test mode or not the server
Affiliation CIELO (TEST) - 1001734898
Access Key (TEST) - e84827130b9837473681c2787007da5914d6359947015a5cdb2b8843db0fa832
Url Return (TEST) - URL / administrator / components / com_virtuemart / classes / payment / pagamento_visa / retorno_visa.php
Affiliation CIELO (PRODUCTION) -
Access Key (PRODUCTION) -
Url Return (PRODUCTION) -
Minimum - 0.01
Plots Max - 10
Interest Installment Type - Customer Issuer or pay interest
Authorization Type - Choose option 2
Capture Transaction or not - Capture the transaction automatically or not