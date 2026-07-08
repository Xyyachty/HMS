<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title>Front Desk - HMS-Learn</title>
    <link rel="icon" type="image/png" href="<?= asset('chtm-logoo.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Manrope', 'sans-serif'] },
                    colors: {
                        brand: '#DB2777',
                        'brand-light': '#F472B6',
                        'brand-dark': '#9D174D',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Manrope', sans-serif; }
        .widget-item { cursor: move; }
        .canvas-zone { min-height: 600px; }
        .widget-on-canvas { position: absolute; cursor: move; }
        .widget-on-canvas.dragging { opacity: 0.5; }
    </style>
</head>
<body class="bg-slate-50">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <img src="<?= asset('chtm-logoo.png') ?>" alt="HMS-Learn" class="h-16 w-auto" />
                    <div>
                        <h1 class="text-lg font-bold">HMS<span class="text-brand">-Learn</span></h1>
                        <p class="text-xs text-slate-400">Front Desk</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= route('students.dashboard') ?>" class="text-sm font-semibold text-slate-500 hover:text-brand">← Back to Dashboard</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold">Front Desk Canvas</h2>
            <p class="text-slate-500">Drag and drop widgets to design your front desk layout</p>
        </div>

        <div class="grid grid-cols-12 gap-6">
            <!-- Widgets Sidebar -->
            <div class="col-span-3 bg-white rounded-xl border border-slate-200 p-4">
                <h3 class="font-bold mb-3">Widgets</h3>
                <div id="widget-palette" class="space-y-2">
                    <div class="widget-item p-3 bg-blue-50 rounded-lg border border-blue-200" data-widget-type="reception">
                        <span class="iconify text-blue-600" data-icon="mdi:desk"></span>
                        <span class="text-sm font-semibold ml-2">Reception Desk</span>
                    </div>
                    <div class="widget-item p-3 bg-green-50 rounded-lg border border-green-200" data-widget-type="checkin">
                        <span class="iconify text-green-600" data-icon="mdi:account-check"></span>
                        <span class="text-sm font-semibold ml-2">Check-in Counter</span>
                    </div>
                    <div class="widget-item p-3 bg-amber-50 rounded-lg border border-amber-200" data-widget-type="keycard">
                        <span class="iconify text-amber-600" data-icon="mdi:key"></span>
                        <span class="text-sm font-semibold ml-2">Key Card Station</span>
                    </div>
                    <div class="widget-item p-3 bg-purple-50 rounded-lg border border-purple-200" data-widget-type="billing">
                        <span class="iconify text-purple-600" data-icon="mdi:receipt"></span>
                        <span class="text-sm font-semibold ml-2">Billing Counter</span>
                    </div>
                </div>

                <div class="mt-6 space-y-2">
                    <button onclick="saveCanvas()" class="w-full py-2 bg-brand text-white rounded-lg font-semibold hover:bg-brand-dark">
                        Save Canvas
                    </button>
                    <button onclick="clearCanvas()" class="w-full py-2 bg-slate-200 text-slate-700 rounded-lg font-semibold hover:bg-slate-300">
                        Clear All
                    </button>
                </div>
            </div>

            <!-- Canvas Area -->
            <div class="col-span-9">
                <div id="canvas-zone" class="canvas-zone bg-white rounded-xl border-2 border-dashed border-slate-300 relative">
                    <!-- Widgets will be dropped here -->
                </div>
            </div>
        </div>
    </main>

    <script>
        const canvasZone = document.getElementById('canvas-zone');
        const widgetPalette = document.getElementById('widget-palette');
        let draggedWidget = null;
        let widgetCounter = 0;
        const groupName = '<?= $group?->name ?? "" ?>';

        // Load saved canvas
        window.addEventListener('load', loadCanvas);

        // Drag from palette
        widgetPalette.addEventListener('dragstart', (e) => {
            if (e.target.classList.contains('widget-item')) {
                draggedWidget = {
                    type: e.target.dataset.widgetType,
                    html: e.target.innerHTML,
                    fromPalette: true
                };
            }
        });

        // Canvas drag events
        canvasZone.addEventListener('dragover', (e) => {
            e.preventDefault();
        });

        canvasZone.addEventListener('drop', (e) => {
            e.preventDefault();
            if (!draggedWidget) return;

            const rect = canvasZone.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            if (draggedWidget.fromPalette) {
                createWidgetOnCanvas(draggedWidget.type, draggedWidget.html, x, y);
            } else {
                draggedWidget.element.style.left = x + 'px';
                draggedWidget.element.style.top = y + 'px';
            }

            draggedWidget = null;
        });

        function createWidgetOnCanvas(type, html, x, y) {
            const widget = document.createElement('div');
            widget.className = 'widget-on-canvas p-3 bg-white rounded-lg shadow-lg border-2';
            widget.style.left = x + 'px';
            widget.style.top = y + 'px';
            widget.draggable = true;
            widget.dataset.widgetId = 'widget-' + (++widgetCounter);
            widget.dataset.widgetType = type;
            widget.innerHTML = html + '<button onclick="removeWidget(this)" class="ml-2 text-red-500 text-xs">×</button>';

            widget.addEventListener('dragstart', (e) => {
                draggedWidget = { element: widget, fromPalette: false };
                widget.classList.add('dragging');
            });

            widget.addEventListener('dragend', () => {
                widget.classList.remove('dragging');
            });

            canvasZone.appendChild(widget);
            logActivity('widget_added', 'Added Widget', type);
        }

        function removeWidget(btn) {
            const widget = btn.closest('.widget-on-canvas');
            widget.remove();
            logActivity('widget_removed', 'Removed Widget', widget.dataset.widgetType);
        }

        function saveCanvas() {
            const widgets = [];
            document.querySelectorAll('.widget-on-canvas').forEach(w => {
                widgets.push({
                    id: w.dataset.widgetId,
                    type: w.dataset.widgetType,
                    x: w.style.left,
                    y: w.style.top
                });
            });

            fetch('<?= route("students.frontdesk.canvas.save") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ canvas_data: widgets, group_name: groupName })
            })
            .then(r => r.json())
            .then(data => {
                alert('Canvas saved successfully!');
                logActivity('canvas_saved', 'Canvas Saved', widgets.length + ' widgets');
            });
        }

        function loadCanvas() {
            if (!groupName) return;

            fetch('<?= route("students.frontdesk.canvas.load") ?>?group_name=' + encodeURIComponent(groupName))
                .then(r => r.json())
                .then(data => {
                    if (data.canvas_data && data.canvas_data.length > 0) {
                        data.canvas_data.forEach(w => {
                            const widgetEl = document.querySelector(`[data-widget-type="${w.type}"]`);
                            if (widgetEl) {
                                createWidgetOnCanvas(w.type, widgetEl.innerHTML, parseInt(w.x), parseInt(w.y));
                            }
                        });
                    }
                });
        }

        function clearCanvas() {
            if (confirm('Clear all widgets?')) {
                canvasZone.innerHTML = '';
                logActivity('canvas_cleared', 'Canvas Cleared', 'All widgets removed');
            }
        }

        function logActivity(action, label, description) {
            if (!groupName) return;

            fetch('<?= route("students.frontdesk.activity.log") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    action: action,
                    action_label: label,
                    description: description,
                    group_name: groupName
                })
            });
        }
    </script>

</body>
</html>
