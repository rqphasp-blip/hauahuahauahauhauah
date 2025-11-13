<div class="modal fade" id="fieldModal" tabindex="-1" aria-labelledby="fieldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="field-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="fieldModalLabel">Adicionar campo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="field-label">Rótulo</label>
                        <input type="text" id="field-label" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="field-name">Nome interno</label>
                        <input type="text" id="field-name" class="form-control" placeholder="opcional">
                        <small class="text-muted">Usado para identificar o campo no envio. Se vazio, será gerado automaticamente.</small>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="field-type">Tipo</label>
                            <select id="field-type" class="form-select">
                                <option value="text">Texto</option>
                                <option value="email">E-mail</option>
                                <option value="tel">Telefone</option>
                                <option value="textarea">Área de texto</option>
                                <option value="select">Seleção (lista)</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="field-required">
                                <label class="form-check-label" for="field-required">Obrigatório</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label" for="field-placeholder">Placeholder</label>
                        <input type="text" id="field-placeholder" class="form-control" placeholder="Ex: Digite seu e-mail">
                    </div>
                    <div class="mb-3 d-none" id="field-options-wrapper">
                        <label class="form-label" for="field-options">Opções (uma por linha)</label>
                        <textarea id="field-options" rows="3" class="form-control" placeholder="Opção 1\nOpção 2"></textarea>
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