@extends('coordinator.layout')

@section('title', 'Event Reports')

@section('styles')
<style>
    /* ===== Main Outer Wrapper ===== */
    .coord-container { max-width: 980px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; padding-top: 4px; }
    
    /* ===== Layer 1: Page Header Floating Panel ===== */
    .coord-card-floating { background: #ffffff; border: 1px solid #ebebeb; border-radius: 16px; padding: 20px 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.02); transition: box-shadow 0.2s ease; }
    .coord-card-floating:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.04); }
    .coord-header-bar { display: flex; flex-direction: column; gap: 2px; }
    .coord-header-bar h2 { font-size: 22px; font-weight: 700; color: var(--text); margin: 0; display: flex; align-items: center; gap: 8px; }
    .coord-header-bar p { color: var(--muted); font-size: 13px; margin: 0; }

    /* ===== Layer 2: Summary Stats Section ===== */
    .reports-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; }
    .stat-layer-card { background: #ffffff; border: 1px solid #ebebeb; border-radius: 14px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
    .stat-layer-info h4 { font-size: 11px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.4px; margin: 0 0 4px; }
    .stat-layer-info span { font-size: 24px; font-weight: 700; color: var(--text); }
    .stat-layer-icon { width: 42px; height: 42px; border-radius: 10px; background: rgba(243,197,71,0.12); color: var(--gold3); display: flex; align-items: center; justify-content: center; font-size: 18px; }

    /* ===== Layer 3: Main Reports List Floating Panel ===== */
    .section-title-group { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid #f4f4f4; padding-bottom: 12px; }
    .section-title-group h3 { font-size: 16px; font-weight: 700; color: var(--text); margin: 0; }
    
    .reports-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; }
    .report-card-item { background: #fafafa; border: 1px solid #ebebeb; border-radius: 14px; padding: 18px; display: flex; flex-direction: column; gap: 8px; transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease; position: relative; }
    .report-card-item:hover { border-color: var(--border2); background: #ffffff; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.03); }
    .report-card-header { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .report-card-item h3 { font-size: 15px; font-weight: 700; margin: 0; color: var(--text); }
    .report-card-item p { font-size: 13px; color: var(--muted); margin: 0; line-height: 1.45; }

    /* Status Pills */
    .status-pill { display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .status-pill.completed { background: rgba(46,159,77,0.1); color: #2e9f4d; }
    .status-pill.pending { background: rgba(243,197,71,0.15); color: #b07c00; }

    /* Card Footer Action */
    .report-card-footer { display: flex; align-items: center; justify-content: space-between; margin-top: auto; padding-top: 10px; border-top: 1px solid #f0f0f0; font-size: 12px; }
    .view-report-link { color: var(--gold3); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; transition: gap 0.2s ease; }
    .view-report-link:hover { gap: 6px; }
</style>
@endsection

@section('content')
<div class="coord-container">
    
    {{-- LAYER 1: Header Banner Card --}}
    <div class="coord-card-floating">
        <header class="coord-header-bar">
            <h2><i class="fas fa-file-alt text-gold"></i> Event Reports</h2>
            <p>Track post-event summaries, client reviews, and completion statuses.</p>
        </header>
    </div>

    {{-- LAYER 2: Overview Statistics Layer --}}
    <div class="reports-stats-grid">
        <div class="stat-layer-card">
            <div class="stat-layer-info">
                <h4>Total Events</h4>
                <span>3</span>
            </div>
            <div class="stat-layer-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
        </div>

        <div class="stat-layer-card">
            <div class="stat-layer-info">
                <h4>Completed</h4>
                <span>2</span>
            </div>
            <div class="stat-layer-icon" style="background: rgba(46,159,77,0.1); color: #2e9f4d;">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>

        <div class="stat-layer-card">
            <div class="stat-layer-info">
                <h4>Pending Review</h4>
                <span>1</span>
            </div>
            <div class="stat-layer-icon" style="background: rgba(243,197,71,0.15); color: #b07c00;">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    {{-- LAYER 3: Main Event Reports Panel --}}
    <div class="coord-card-floating">
        <div class="section-title-group">
            <h3>Recent Reports</h3>
        </div>

        <div class="reports-grid">
            <div class="report-card-item">
                <div class="report-card-header">
                    <h3>Wedding Event</h3>
                    <span class="status-pill completed">Completed</span>
                </div>
                <p>Successfully managed full wedding event with catering and decorations delivered on time.</p>
                <div class="report-card-footer">
                    <span style="color: var(--muted);">Sep 05, 2026</span>
                    <a href="#" class="view-report-link">View Details <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <div class="report-card-item">
                <div class="report-card-header">
                    <h3>Birthday Celebration</h3>
                    <span class="status-pill pending">Pending Review</span>
                </div>
                <p>Waiting for final client feedback and payment confirmation.</p>
                <div class="report-card-footer">
                    <span style="color: var(--muted);">Sep 08, 2026</span>
                    <a href="#" class="view-report-link">View Details <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <div class="report-card-item">
                <div class="report-card-header">
                    <h3>Corporate Seminar</h3>
                    <span class="status-pill completed">Completed</span>
                </div>
                <p>Audio system, stage setup, and food service handled successfully.</p>
                <div class="report-card-footer">
                    <span style="color: var(--muted);">Aug 28, 2026</span>
                    <a href="#" class="view-report-link">View Details <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection