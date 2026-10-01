@extends('layouts.master')

@section('page_title')
    {{ __('msg.recordings') }} - {{ $meeting->meeting_name }}
@endsection

@section('content')
<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-0">{{ $meeting->meeting_name }}</h1>
                <small class="text-muted">{{ __('msg.class_recordings') }}</small>
            </div>
            <div class="col-md-4 text-right">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">{{ __('msg.back') }}</a>
            </div>
        </div>
    </div>

    <div class="page-content container-fluid mt-3">
        @include('includes.alerts')

        @if($recordings->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="bg-gradient-primary text-white">
                        <tr>
                            <th>{{ __('msg.name') }}</th>
                            <th>{{ __('msg.duration') }}</th>
                            <th>{{ __('msg.date') }}</th>
                            <th>{{ __('msg.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recordings as $recording)
                            <tr>
                                <td>
                                    <strong>{{ $recording->name }}</strong>
                                </td>
                                <td>
                                    {{ intdiv($recording->duration, 60) }}:{{ str_pad($recording->duration % 60, 2, '0', STR_PAD_LEFT) }}
                                </td>
                                <td>
                                    {{ $recording->created_at->format('d M Y H:i') }}
                                </td>
                                <td>
                                    @if($recording->playback_url)
                                        <a href="{{ $recording->playback_url }}" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-play-circle"></i> {{ __('msg.watch') }}
                                        </a>
                                    @endif

                                    @if($recording->download_url)
                                        <a href="{{ $recording->download_url }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-download"></i> {{ __('msg.download') }}
                                        </a>
                                    @endif

                                    @if(Qs::userIsTeamAccount() && Qs::userIsTeamSA())
                                        <form action="{{ route('bbb.delete_recording', $recording->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" onclick="return confirm('{{ __('msg.confirm_delete') }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    {{ __('msg.no_recordings') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info text-center">
                <i class="fas fa-video"></i> No Recordings Available
            </div>
        @endif
    </div>
</div>
@endsection
