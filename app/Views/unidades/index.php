<?php
$pageTitle = 'Cadastro Unidade';
$currentUser = $currentUser ?? null;
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/nav.php';
?>
<div class="container py-4">
    <h1 class="mb-4">Cadastro Unidade</h1>
    <?php require __DIR__ . '/../partials/flash.php'; ?>
    <div class="card mb-4">
        <div class="card-header">Nova unidade</div>
        <div class="card-body">
            <form method="post" action="/unidades">
                <?= csrf_field() ?>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Unidade *</label>
                        <input type="text" name="name" class="form-control" placeholder="Nome da unidade" required minlength="2">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Endereço</label>
                        <input type="text" name="endereco" class="form-control" placeholder="Endereço completo">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bairro</label>
                        <input type="text" name="bairro" class="form-control" placeholder="Bairro">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cidade</label>
                        <input type="text" name="cidade" class="form-control" placeholder="Cidade">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">UF</label>
                        <input type="text" name="uf" class="form-control" placeholder="UF" maxlength="5">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">CEP</label>
                        <input type="text" name="cep" class="form-control" placeholder="00000-000" maxlength="9" title="Digite o CEP para buscar o endereço automaticamente">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Centro de lucro</label>
                        <input type="text" name="centro_de_lucro" class="form-control" placeholder="Centro de lucro">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sigla</label>
                        <input type="text" name="sigla" class="form-control" placeholder="Sigla" maxlength="20">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Criar</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>Unidades cadastradas</span>
            <form method="get" action="/unidades" class="d-flex gap-2">
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Filtrar por nome, sigla, cidade, endereço..." value="<?= e($filtro ?? '') ?>" style="min-width:220px">
                <button type="submit" class="btn btn-sm btn-outline-primary">Filtrar</button>
                <?php if (!empty($filtro ?? '')): ?>
                <a href="/unidades" class="btn btn-sm btn-outline-secondary">Limpar</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Unidade</th>
                            <th>Endereço</th>
                            <th>Bairro</th>
                            <th>Cidade</th>
                            <th>UF</th>
                            <th>CEP</th>
                            <th>Centro de lucro</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unidades as $u): ?>
                        <tr>
                            <td><?= e((string) $u['id']) ?></td>
                            <td><?= e($u['name']) ?><?= !empty($u['sigla']) ? ' <small class="text-muted">(' . e($u['sigla']) . ')</small>' : '' ?></td>
                            <td><?= e($u['endereco'] ?? '-') ?></td>
                            <td><?= e($u['bairro'] ?? '-') ?></td>
                            <td><?= e($u['cidade'] ?? '-') ?></td>
                            <td><?= e($u['uf'] ?? '-') ?></td>
                            <td><?= e($u['cep'] ?? '-') ?></td>
                            <td><?= e($u['centro_de_lucro'] ?? '-') ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $u['id'] ?>">Editar</button>
                                <form method="post" action="/unidades/<?= $u['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Excluir unidade?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                        <div class="modal fade" id="editModal<?= $u['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="post" action="/unidades/<?= $u['id'] ?>/update">
                                        <?= csrf_field() ?>
                                        <div class="modal-header">
                                            <h5 class="modal-title">Editar unidade</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-2">
                                                <label class="form-label">Unidade *</label>
                                                <input type="text" name="name" class="form-control" value="<?= e($u['name']) ?>" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Endereço</label>
                                                <input type="text" name="endereco" class="form-control" value="<?= e($u['endereco'] ?? '') ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Bairro</label>
                                                <input type="text" name="bairro" class="form-control" value="<?= e($u['bairro'] ?? '') ?>">
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-2">
                                                    <label class="form-label">Cidade</label>
                                                    <input type="text" name="cidade" class="form-control" value="<?= e($u['cidade'] ?? '') ?>">
                                                </div>
                                                <div class="col-3 mb-2">
                                                    <label class="form-label">UF</label>
                                                    <input type="text" name="uf" class="form-control" value="<?= e($u['uf'] ?? '') ?>" maxlength="5">
                                                </div>
                                                <div class="col-3 mb-2">
                                                    <label class="form-label">CEP</label>
                                                    <input type="text" name="cep" class="form-control" value="<?= e($u['cep'] ?? '') ?>" placeholder="00000-000" maxlength="9">
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Centro de lucro</label>
                                                <input type="text" name="centro_de_lucro" class="form-control" value="<?= e($u['centro_de_lucro'] ?? '') ?>">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Sigla</label>
                                                <input type="text" name="sigla" class="form-control" value="<?= e($u['sigla'] ?? '') ?>" maxlength="20">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Salvar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
(function(){
    function apenasNumeros(s){ return (s||'').replace(/\D/g,''); }
    function buscarCep(cepInput){
        var cep=apenasNumeros(cepInput.value);
        if(cep.length!==8)return;
        var form=cepInput.closest('form');
        if(!form)return;
        var endereco=form.querySelector('[name="endereco"]'),bairro=form.querySelector('[name="bairro"]'),cidade=form.querySelector('[name="cidade"]'),uf=form.querySelector('[name="uf"]');
        if(!endereco&&!bairro&&!cidade&&!uf)return;
        fetch('https://viacep.com.br/ws/'+cep+'/json/').then(function(r){return r.json();}).then(function(d){
            if(d.erro){if(typeof alert==='function')alert('CEP não encontrado.');return;}
            if(endereco)endereco.value=d.logradouro||'';
            if(bairro)bairro.value=d.bairro||'';
            if(cidade)cidade.value=d.localidade||'';
            if(uf)uf.value=d.uf||'';
            cepInput.value=(d.cep||cep).replace(/^(\d{5})(\d{3})$/,'$1-$2');
        }).catch(function(){if(typeof alert==='function')alert('Erro ao buscar CEP. Tente novamente.');});
    }
    document.querySelectorAll('input[name="cep"]').forEach(function(inp){
        inp.addEventListener('blur',function(){buscarCep(this);});
        inp.addEventListener('input',function(){
            if(apenasNumeros(this.value).length===8)buscarCep(this);
        });
    });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
