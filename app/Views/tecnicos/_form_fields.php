<?php
$t = $t ?? $old ?? [];
$t = is_array($t) ? $t : [];
$v = fn($k) => e($t[$k] ?? '');
?>
<div class="row mb-3">
    <div class="col-12"><h6 class="border-bottom pb-1">Dados pessoais</h6></div>
    <div class="col-md-4 mb-2"><label class="form-label">NOME *</label><input type="text" name="name" class="form-control" required minlength="2" value="<?= $v('name') ?>"></div>
    <div class="col-md-4 mb-2"><label class="form-label">NATURALIDADE</label><input type="text" name="naturalidade" class="form-control" value="<?= $v('naturalidade') ?>"></div>
    <div class="col-md-4 mb-2"><label class="form-label">EMAIL</label><input type="email" name="email" class="form-control" value="<?= $v('email') ?>"></div>
    <div class="col-md-3 mb-2"><label class="form-label">RG</label><input type="text" name="rg" class="form-control" value="<?= $v('rg') ?>"></div>
    <div class="col-md-3 mb-2"><label class="form-label">CPF</label><input type="text" name="cpf" class="form-control" value="<?= $v('cpf') ?>"></div>
    <div class="col-md-3 mb-2"><label class="form-label">DATA DE NASCIMENTO</label><input type="date" name="data_nascimento" class="form-control" value="<?= $v('data_nascimento') ?>"></div>
    <div class="col-md-3 mb-2"><label class="form-label">GENERO</label><input type="text" name="genero" class="form-control" value="<?= $v('genero') ?>"></div>
    <div class="col-md-6 mb-2"><label class="form-label">NOME DA MAE</label><input type="text" name="nome_mae" class="form-control" value="<?= $v('nome_mae') ?>"></div>
    <div class="col-md-6 mb-2"><label class="form-label">NOME DO PAI</label><input type="text" name="nome_pai" class="form-control" value="<?= $v('nome_pai') ?>"></div>
</div>

<div class="row mb-3">
    <div class="col-12"><h6 class="border-bottom pb-1">Endereço</h6></div>
    <div class="col-md-2 mb-2"><label class="form-label">CEP</label><input type="text" name="cep" class="form-control" placeholder="00000-000" maxlength="9" value="<?= $v('cep') ?>" title="Digite o CEP para buscar o endereço automaticamente"></div>
    <div class="col-md-4 mb-2"><label class="form-label">ENDERECO</label><input type="text" name="endereco" class="form-control" value="<?= $v('endereco') ?>"></div>
    <div class="col-md-1 mb-2"><label class="form-label">Nº</label><input type="text" name="numero" class="form-control" value="<?= $v('numero') ?>"></div>
    <div class="col-md-2 mb-2"><label class="form-label">BAIRRO</label><input type="text" name="bairro" class="form-control" value="<?= $v('bairro') ?>"></div>
    <div class="col-md-2 mb-2"><label class="form-label">CIDADE</label><input type="text" name="cidade" class="form-control" value="<?= $v('cidade') ?>"></div>
    <div class="col-md-1 mb-2"><label class="form-label">ESTADO</label><input type="text" name="estado" class="form-control" maxlength="2" value="<?= $v('estado') ?>"></div>
</div>

<div class="row mb-3">
    <div class="col-12"><h6 class="border-bottom pb-1">Contatos</h6></div>
    <div class="col-md-3 mb-2"><label class="form-label">CELULAR 1</label><input type="text" name="celular_1" class="form-control" value="<?= $v('celular_1') ?>"></div>
    <div class="col-md-3 mb-2"><label class="form-label">CELULAR 2</label><input type="text" name="celular_2" class="form-control" value="<?= $v('celular_2') ?>"></div>
    <div class="col-md-3 mb-2"><label class="form-label">WHATSAPP</label><input type="text" name="whats" class="form-control" value="<?= $v('whats') ?>"></div>
    <div class="col-md-3 mb-2"><label class="form-label">TELEFONE FIXO</label><input type="text" name="telefone_fixo" class="form-control" value="<?= $v('telefone_fixo') ?>"></div>
    <div class="col-md-3 mb-2"><label class="form-label">TELEFONE</label><input type="text" name="telefone" class="form-control" value="<?= $v('telefone') ?>"></div>
</div>

<div class="row mb-3">
    <div class="col-12"><h6 class="border-bottom pb-1">Dados bancários</h6></div>
    <div class="col-md-6 mb-2"><label class="form-label">REFERENCIA BANCARIA</label><input type="text" name="referencia_bancaria" class="form-control" value="<?= $v('referencia_bancaria') ?>"></div>
    <div class="col-md-6 mb-2"><label class="form-label">CHAVE PIX</label><input type="text" name="chave_pix" class="form-control" value="<?= $v('chave_pix') ?>"></div>
    <div class="col-md-4 mb-2"><label class="form-label">BANCO</label><input type="text" name="banco" class="form-control" value="<?= $v('banco') ?>"></div>
    <div class="col-md-2 mb-2"><label class="form-label">COD BANCO</label><input type="text" name="cod_banco" class="form-control" value="<?= $v('cod_banco') ?>"></div>
    <div class="col-md-2 mb-2"><label class="form-label">AGENCIA</label><input type="text" name="agencia" class="form-control" value="<?= $v('agencia') ?>"></div>
    <div class="col-md-2 mb-2"><label class="form-label">CONTA</label><input type="text" name="conta" class="form-control" value="<?= $v('conta') ?>"></div>
    <div class="col-md-2 mb-2"><label class="form-label">TIPO DE CONTA</label><input type="text" name="tipo_conta" class="form-control" placeholder="Corrente ou Poupança" value="<?= $v('tipo_conta') ?>"></div>
    <div class="col-md-2 mb-2"><label class="form-label">OPERACAO</label><input type="text" name="operacao" class="form-control" value="<?= $v('operacao') ?>"></div>
    <div class="col-md-4 mb-2"><label class="form-label">FAVORECIDO</label><input type="text" name="favorecido" class="form-control" value="<?= $v('favorecido') ?>"></div>
</div>

<div class="row mb-3">
    <div class="col-12"><h6 class="border-bottom pb-1">Empresa (se for empresa)</h6></div>
    <div class="col-md-4 mb-2"><label class="form-label">RAZAO SOCIAL</label><input type="text" name="razao_social" class="form-control" value="<?= $v('razao_social') ?>"></div>
    <div class="col-md-4 mb-2"><label class="form-label">NOME FANTASIA</label><input type="text" name="nome_fantasia" class="form-control" value="<?= $v('nome_fantasia') ?>"></div>
    <div class="col-md-4 mb-2"><label class="form-label">CNPJ</label><input type="text" name="cnpj" class="form-control" value="<?= $v('cnpj') ?>"></div>
    <div class="col-md-4 mb-2"><label class="form-label">INSCRICAO ESTADUAL</label><input type="text" name="inscricao_estadual" class="form-control" value="<?= $v('inscricao_estadual') ?>"></div>
    <div class="col-md-4 mb-2"><label class="form-label">INSCRICAO MUNICIPAL</label><input type="text" name="inscricao_municipal" class="form-control" value="<?= $v('inscricao_municipal') ?>"></div>
    <div class="col-md-2 mb-2"><label class="form-label">CEP</label><input type="text" name="empresa_cep" class="form-control" placeholder="00000-000" maxlength="9" value="<?= $v('empresa_cep') ?>" title="Digite o CEP para buscar o endereço automaticamente"></div>
    <div class="col-md-4 mb-2"><label class="form-label">ENDERECO</label><input type="text" name="empresa_endereco" class="form-control" value="<?= $v('empresa_endereco') ?>"></div>
    <div class="col-md-1 mb-2"><label class="form-label">Nº</label><input type="text" name="empresa_numero" class="form-control" value="<?= $v('empresa_numero') ?>"></div>
    <div class="col-md-2 mb-2"><label class="form-label">BAIRRO</label><input type="text" name="empresa_bairro" class="form-control" value="<?= $v('empresa_bairro') ?>"></div>
    <div class="col-md-2 mb-2"><label class="form-label">CIDADE</label><input type="text" name="empresa_cidade" class="form-control" value="<?= $v('empresa_cidade') ?>"></div>
    <div class="col-md-1 mb-2"><label class="form-label">ESTADO</label><input type="text" name="empresa_estado" class="form-control" maxlength="2" value="<?= $v('empresa_estado') ?>"></div>
    <div class="col-md-6 mb-2"><label class="form-label">REFERENCIA BANCARIA DA EMPRESA</label><input type="text" name="empresa_referencia_bancaria" class="form-control" value="<?= $v('empresa_referencia_bancaria') ?>"></div>
    <div class="col-md-6 mb-2"><label class="form-label">CHAVE PIX</label><input type="text" name="empresa_chave_pix" class="form-control" value="<?= $v('empresa_chave_pix') ?>"></div>
    <div class="col-md-4 mb-2"><label class="form-label">BANCO</label><input type="text" name="empresa_banco" class="form-control" value="<?= $v('empresa_banco') ?>"></div>
    <div class="col-md-2 mb-2"><label class="form-label">COD BANCO</label><input type="text" name="empresa_cod_banco" class="form-control" value="<?= $v('empresa_cod_banco') ?>"></div>
    <div class="col-md-2 mb-2"><label class="form-label">AGENCIA</label><input type="text" name="empresa_agencia" class="form-control" value="<?= $v('empresa_agencia') ?>"></div>
    <div class="col-md-2 mb-2"><label class="form-label">CONTA</label><input type="text" name="empresa_conta" class="form-control" value="<?= $v('empresa_conta') ?>"></div>
    <div class="col-md-2 mb-2"><label class="form-label">TIPO DE CONTA</label><input type="text" name="empresa_tipo_conta" class="form-control" value="<?= $v('empresa_tipo_conta') ?>"></div>
    <div class="col-md-2 mb-2"><label class="form-label">OPERACAO</label><input type="text" name="empresa_operacao" class="form-control" value="<?= $v('empresa_operacao') ?>"></div>
    <div class="col-md-4 mb-2"><label class="form-label">FAVORECIDO</label><input type="text" name="empresa_favorecido" class="form-control" value="<?= $v('empresa_favorecido') ?>"></div>
</div>
<script>
(function(){
    function apenasNumeros(s){ return (s||'').replace(/\D/g,''); }
    function buscarCep(cepInput, fieldPrefix){
        var cep=apenasNumeros(cepInput.value);
        if(cep.length!==8)return;
        var form=cepInput.closest('form');
        if(!form)return;
        var endereco=form.querySelector('[name="'+(fieldPrefix||'')+'endereco"]');
        var bairro=form.querySelector('[name="'+(fieldPrefix||'')+'bairro"]');
        var cidade=form.querySelector('[name="'+(fieldPrefix||'')+'cidade"]');
        var estado=form.querySelector('[name="'+(fieldPrefix||'')+'estado"]');
        if(!endereco&&!bairro&&!cidade&&!estado)return;
        fetch('https://viacep.com.br/ws/'+cep+'/json/').then(function(r){return r.json();}).then(function(d){
            if(d.erro){if(typeof alert==='function')alert('CEP não encontrado.');return;}
            if(endereco)endereco.value=d.logradouro||'';
            if(bairro)bairro.value=d.bairro||'';
            if(cidade)cidade.value=d.localidade||'';
            if(estado)estado.value=d.uf||'';
            cepInput.value=(d.cep||cep).replace(/^(\d{5})(\d{3})$/,'$1-$2');
        }).catch(function(){if(typeof alert==='function')alert('Erro ao buscar CEP. Tente novamente.');});
    }
    document.querySelectorAll('input[name="cep"]').forEach(function(inp){
        inp.addEventListener('blur',function(){buscarCep(this,'');});
        inp.addEventListener('input',function(){if(apenasNumeros(this.value).length===8)buscarCep(this,'');});
    });
    document.querySelectorAll('input[name="empresa_cep"]').forEach(function(inp){
        inp.addEventListener('blur',function(){buscarCep(this,'empresa_');});
        inp.addEventListener('input',function(){if(apenasNumeros(this.value).length===8)buscarCep(this,'empresa_');});
    });
})();
</script>
