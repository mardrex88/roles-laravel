@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">Roles</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @can('crear-rol')
                            <a class="btn btn-warning" href="{{ route('roles.create') }}">Nuevo</a>
                            @endcan
                            <table class="table table-striped mt-2">
                                <thead style="background-color:#6777ef;">
                                    <th style="display: none;">ID</th>
                                    <th style=" color:#fff;">Nombre</th>
                                    <th style=" color:#fff;">Acciones</th>
                                </thead>
                                <tbody>
                                    @foreach($roles as $rol)
                                    <tr>
                                    <td style="display:none;">{{ $rol->id}}</td>
                                    <td>{{ $rol->name}}</td>
                                    <td>
                                        <a class="btn btn-info" href="{{ route('roles.edit', $rol->id) }}">Editar</a>
                                        {!! Form::open(['method'=>'DELETE','route'=>['roles.destroy',$rol->id], 'style'=> 'display:inline'])!!}
                                            {!! Form::submit('Borrar',['class'=>'btn btn-danger'])!!}                                        
                                        {!! Form::close()!!}
                                    </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div>
                                {!! $roles->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

