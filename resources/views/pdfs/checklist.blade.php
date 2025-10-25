<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Checklist #{{ $checklist->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            background-color: #f0f0f0;
            padding: 5px;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .items-list {
            margin-left: 20px;
        }
        .item {
            margin-bottom: 5px;
        }
        .photos-section {
            margin-top: 20px;
        }
        .photo-item {
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Checklist de Serviço</h1>
        <h2>#{{ $checklist->id }}</h2>
    </div>

    <div class="section">
        <div class="section-title">Informações do Serviço</div>
        <div class="info-row">
            <span class="label">Serviço ID:</span>
            <span>{{ $checklist->service_id }}</span>
        </div>
        <div class="info-row">
            <span class="label">Tipo:</span>
            <span>{{ $checklist->service->type ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="label">Status:</span>
            <span>{{ ucfirst($checklist->status) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Data:</span>
            <span>{{ $checklist->created_at->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Informações do Veículo</div>
        @if($checklist->service->vehicle)
        <div class="info-row">
            <span class="label">Marca:</span>
            <span>{{ $checklist->service->vehicle->brand }}</span>
        </div>
        <div class="info-row">
            <span class="label">Modelo:</span>
            <span>{{ $checklist->service->vehicle->model }}</span>
        </div>
        <div class="info-row">
            <span class="label">Placa:</span>
            <span>{{ $checklist->service->vehicle->plate }}</span>
        </div>
        <div class="info-row">
            <span class="label">Ano:</span>
            <span>{{ $checklist->service->vehicle->year }}</span>
        </div>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Itens Verificados</div>
        <div class="items-list">
            @foreach($checklist->items as $item)
            <div class="item">• {{ $item }}</div>
            @endforeach
        </div>
    </div>

    @if($checklist->observations)
    <div class="section">
        <div class="section-title">Observações</div>
        <div>{{ $checklist->observations }}</div>
    </div>
    @endif

    <div class="section">
        <div class="section-title">Responsável</div>
        <div class="info-row">
            <span class="label">Nome:</span>
            <span>{{ $checklist->user->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="label">Oficina:</span>
            <span>{{ $checklist->office->name ?? 'N/A' }}</span>
        </div>
    </div>

    @if($checklist->photos && count($checklist->photos) > 0)
    <div class="photos-section">
        <div class="section-title">Fotos do Checklist</div>
        @foreach($checklist->photos as $photo)
        <div class="photo-item">
            <div class="info-row">
                <span class="label">Arquivo:</span>
                <span>{{ $photo->filename }}</span>
            </div>
            @if($photo->description)
            <div class="info-row">
                <span class="label">Descrição:</span>
                <span>{{ $photo->description }}</span>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <div style="margin-top: 40px; text-align: center; font-size: 10px; color: #666;">
        <p>Documento gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
