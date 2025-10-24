@extends(\'layouts.sidebar\')

@section(\'content\')

<script src="{{ asset(\'resources/ckeditor/ckeditor.js\') }}"></script>

<div class="conatiner-fluid content-inner mt-n5 py-0">
  <div class="row">   

    {{-- Formulário para Configurações de Aparência do Título --}}
    <form action="{{ route(\'settings.appearance.update\') }}" method="post" id="appearanceSettingsForm">
      @csrf
      <div class="col-lg-12">
        <div class="card rounded">
           <div class="card-body">
              <div class="row">
                  <div class="col-sm-12">  
                        <h3>Configurações de Aparência do Título</h3><br>

                        <div class="form-group col-lg-8">
                          <label for="size_title">Tamanho da fonte do titulo (ex: 16, 20, 24):</label>
                          <input type="number" class="form-control" id="size_title" name="size_title" value="{{ old(\'size_title\', Auth::user()->size_title ?? \'\') }}" placeholder="Deixe em branco para o padrão">
                        </div>
                        
                        <div class="form-group col-lg-8 mt-3">
                          <div class="form-check">
                              <input type="hidden" name="hide_title" value="0"> {{-- Envia 0 se o checkbox não for marcado --}}
                              <input type="checkbox" class="form-check-input" id="hide_title" name="hide_title" value="1" {{ old(\'hide_title\', Auth::user()->hide_title ?? false) ? \'checked\' : \'\' }}>
                              <label class="form-check-label" for="hide_title">Ocultar titulo</label>
                          </div>
                        </div>
                        <button type="submit" class="mt-3 ml-3 btn btn-primary">{{__(\'messages.Save\')}} Configurações de Aparência</button>
                  </div>
              </div>
           </div>
        </div>
     </div>
    </form>
    {{-- Fim do Formulário de Aparência --}}

    {{-- Formulário para Páginas de Conteúdo (Terms, Privacy, Contact) --}}
    <form action="{{ route(\'editSitePage\') }}" method="post" id="contentPagesForm">
      @csrf
      @foreach($pages as $page)

      <div class="col-lg-12">
          <div class="card rounded">
             <div class="card-body">
                <div class="row">
                    <div class="col-sm-12">  
                          <div class="form-group col-lg-8">
                            <h3>{{footer(\'Terms\')}}</h3><br>
                            <textarea class="form-control ckeditor" name="terms" rows="3">{{ old(\'terms\', $page->terms ?? \'\') }}</textarea>
                          </div>
                          <button type="submit" class="mt-3 ml-3 btn btn-primary">{{__(\'messages.Save\')}} Termos</button>
                    </div>
                </div>
             </div>
          </div>
       </div>

        <div class="col-lg-12">
            <div class="card rounded">
               <div class="card-body">
                  <div class="row">
                      <div class="col-sm-12">  
                            <div class="form-group col-lg-8">
                              <h3>{{footer(\'Privacy\')}}</h3><br>
                              <textarea class="form-control ckeditor" name="privacy" rows="3">{{ old(\'privacy\', $page->privacy ?? \'\') }}</textarea>
                            </div>
                            <button type="submit" class="mt-3 ml-3 btn btn-primary">{{__(\'messages.Save\')}} Privacidade</button>
                      </div>
                  </div>
               </div>
            </div>
         </div>

          <div class="col-lg-12">
              <div class="card rounded">
                 <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">  
                              <div class="form-group col-lg-8">
                                <h3>{{footer(\'Contact\')}}</h3><br>
                                <textarea class="form-control ckeditor" name="contact" rows="3">{{ old(\'contact\', $page->contact ?? \'\') }}</textarea>
                              </div>
                              <button type="submit" class="mt-3 ml-3 btn btn-primary">{{__(\'messages.Save\')}} Contato</button>
                        </div>
                    </div>
                 </div>
              </div>
           </div>
  
      @endforeach
    </form> 
    {{-- Fim do Formulário de Conteúdo --}}

  </div>
</div>

@endsection

