@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">Lista de Ordenes</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @can('crear-rol')
                            <a class="btn btn-warning" href="{{ route('ordenes.create') }}">Nuevo</a>
                            @endcan
                            <table class="table table-striped mt-2">
                                <thead style="background-color:#6777ef;">
                                    <th style="display: none;">ID</th>
                                    <th style=" color:#fff;">Cliente</th>
                                    <th style=" color:#fff;">Fecha</th>
                                    <th style=" color:#fff;">Recolección</th>
                                    <th style=" color:#fff;">Entrega</th>
                                    <th style=" color:#fff;">Precio</th>
                                    <th style=" color:#fff;">Estado</th>
                                    <th style=" color:#fff;">ID CRM</th>
                                    <th style=" color:#fff;">Accion</th>
                                </thead>
                                <tbody>
                                    @foreach($ordenes as $orden)
                                    <tr>
                                    <td style="display:none;"></td>
                                    <td>N</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div>
                           
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

