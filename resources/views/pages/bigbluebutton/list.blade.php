@extends('layouts.master')

@section('page_title')
    {{ __('msg.recordings') }}
@endsection

@section('content')
<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-0">{{ __('msg.available_recordings') }}</h1>
            </div>
            <div class="col-md-4 text-right">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">{{ __('msg.back') }}</a>
            </div>
        </div>
    </div>

    <div class="page-content container-fluid mt-3">
        @include('includes.alerts')

        @if($recordings->count() > 0)
            <div class="row">
                @forelse($recordings as $recording)
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-gradient-success text-white">
                                <h5 class="mb-0 font-weight-bold">
                                    {{ $recording->name }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <small class="text-muted">{{ __('msg.duration') }}</small>
                                        <p class="font-weight-bold">
                                            {{ intdiv($recording->duration, 60) }}:{{ str_pad($recording->duration % 60, 2, '0', STR_PAD_LEFT) }}
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">{{ __('msg.date') }}</small>
                                        <p class="font-weight-bold">
                                            {{ $recording->created_at->format('d M Y H:i') }}
                                        </p>
                                    </div>
                                </div>

                                @if($recording->playback_url)
                                    <p class="mb-3">
                                        <a href="{{ $recording->playback_url }}" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-play-circle"></i> {{ __('msg.watch') }}
                                        </a>
                                    </p>
                                @endif

                                @if($recording->download_url)
                                    <p class="mb-3">
                                        <a href="{{ $recording->download_url }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-download"></i> {{ __('msg.download') }}
                                        </a>
                                    </p>
                                @endif

                                @if(Qs::userIsTeamAccount() && Qs::userIsTeamSA())
                                    <form action="{{ route('bbb.delete_recording', $recording->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('{{ __('msg.confirm_delete') }}')">
                                            <i class="fas fa-trash"></i> {{ __('msg.delete') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> {{ __('msg.no_recordings') }}
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $recordings->links() }}
            </div>
        @else
            <div class="alert alert-info text-center">
                <i class="fas fa-video"></i> {{ __('msg.no_recordings_available') }}
            </div>
        @endif
    </div>
</div>
@endsection
