@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card bg-black">
                    <div class="card-header text-white">{{ __('Dashboard') }}</div>

                    <div class="card-body bg-black">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <h3 class="mt-3 text-center text-white">Скачать аудио1 с YT:</h3>
                        <div class="mb-3 mt-3">
                            <div class="d-flex justify-content-between">
                                <div class="form-check form-switch d-flex align-items-center">
                                    <input class="form-check-input mb-1" type="checkbox" role="switch"
                                           id="switchUrl">
                                    <label class="form-check-label mx-2 text-white"
                                           for="switchUrl">ID вместо ссылки</label>
                                </div>
                                <button type="button" class="btn btn-danger" aria-label="Очистить"
                                        id="clear-btn">Очистить
                                </button>
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control d-block my-3"
                                       id="address" value="">
                                <div id="error" class="text-danger"></div>
                                <div id="name" class="text-success"></div>
                                <button class="btn btn-primary mt-2" type="button"
                                        id="get-file-btn" disabled>
                                    Получить файл
                                </button>
                            </div>
                            <div id="res" class="text-center text-lg-start"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
