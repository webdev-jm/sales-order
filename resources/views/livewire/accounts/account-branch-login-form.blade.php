<div class="modal-content">

    <style>
        .img {
            max-width: 100px;
            min-height: 50px;
            max-height: 100px;
        }
    </style>

    @section('plugins.EkkoLightbox', true)

    <form wire:submit.prevent="logout" enctype="multipart/form-data">
        <div class="modal-header">
            <h4 class="modal-title">Logout Branch <span class="badge badge-primary">[{{$branch->branch_code}}] {{$branch->branch_name}}</span></h4>
        </div>
        <div class="modal-body text-left">

            @if(!empty($logged_branch->operation_process_id))
            <h5>ACTUAL ACTIVITIES:</h5>

            <div class="row mb-3">
                <div class="col-12">
                    <label>{{$logged_branch->operation_process->operation_process}}</label>
                    <ol>
                        @foreach($branch_activities as $activity)
                        <li>{{$activity->activity->description}}
                            @if(!empty($activity->remarks))
                            <ul>
                                <li>{{$activity->remarks}}</li>
                            </ul>
                            @endif
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>
            @elseif(!empty($logged_branch->login_activities()->count()))
                <h5 class="mb-0">ACTUAL ACTIVITY:</h5>
                <p class="ml-3">{{$logged_branch->login_activities()->first()->remarks}}</p>
            @endif

            <h5 class="mb-0">ACTION POINTS:</h5>
            <p class="ml-3">{{$logged_branch->action_points}}</p>

            <div class="row">

                @if($picture_file)
                    <label>Preview</label>
                    <div class="col-12 py-2">
                        @foreach($picture_file as $file)
                            <a href="{{ $file->temporaryUrl() }}" data-toggle="lightbox" data-title="Preview">
                                <img src="{{ $file->temporaryUrl() }}" class="mx-auto d-inline img" height="300px">
                            </a>
                        @endforeach
                    </div>
                @elseif(file_exists($image_url.'/small.jpg'))
                    <label>Preview</label>
                    <div class="col-12 py-2">
                        <a href="{{ asset('uploads/account-login/'.$logged->user_id.'/'.$logged->id).'/large.jpg' }}" data-toggle="lightbox" data-title="Preview">
                            <img src="{{ asset('uploads/account-login/'.$logged->user_id.'/'.$logged->id).'/large.jpg' }}" class="mx-auto d-inline img" height="300px">
                        </a>
                    </div>
                @endif

                <div class="col-md-12">
                    <div class="form-group">
                        <div class="custom-file text-center">
                            <button type="button" class="btn btn-primary" id="openCameraBtn" wire:loading.attr="disabled">
                                <i class="fas fa-camera"></i> Open Camera
                            </button>

                            <div id="cameraStream" class="mt-2" style="display:none;">
                                <video id="video" autoplay playsinline style="max-width:100%; transform: scaleX(-1);"></video>

                                <div class="mt-2">
                                    <button type="button" class="btn btn-success" id="captureBtn">Capture</button>
                                    <button type="button" class="btn btn-secondary" id="retakeBtn" style="display:none;">Retake</button>
                                    <button type="button" class="btn btn-danger" id="closeCameraBtn">Close</button>
                                </div>

                                <canvas id="canvas" style="display:none;"></canvas>

                                <div id="preview" class="mt-2" style="display:none;">
                                    <label>Preview</label>
                                    <img id="previewImg" class="mx-auto d-inline img" />
                                </div>
                            </div>

                            <p class="text-danger">{{$errors->first('picture_file')}}</p>
                        </div>
                    </div>
                </div>

            </div>

            @if(!$longitude && !$latitude)
                <strong class="text-danger">Please enable your location before signing out thank you!</strong>
            @endif

        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            @if($longitude && $latitude)
                <button type="submit" class="btn btn-danger" wire:loading.attr="disabled">Sign Out</button>
            @endif
        </div>
    </form>

    <script>
        document.addEventListener('livewire:load', function () {

            $('body').on('click', '[data-toggle="lightbox"]', function(event) {
                event.preventDefault();
                $(this).ekkoLightbox({
                    alwaysShowClose: true
                });
            });

            window.addEventListener('reloadLocation', event => {
                getLocation();
            });

            getLocation();
            function getLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        @this.accuracy = position.coords.accuracy.toFixed(3)+' m';
                        @this.longitude = position.coords.longitude;
                        @this.latitude = position.coords.latitude;
                    }, function(error) {
                        @this.accuracy = error.message;
                    });
                } else {
                    console.log("Geolocation is not supported by this browser.");
                }
            }

        });

        document.addEventListener('livewire:load', function () {
            let stream = null;
            const openBtn = document.getElementById('openCameraBtn');
            const closeBtn = document.getElementById('closeCameraBtn');
            const captureBtn = document.getElementById('captureBtn');
            const retakeBtn = document.getElementById('retakeBtn');
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const preview = document.getElementById('preview');
            const previewImg = document.getElementById('previewImg');
            const cameraStream = document.getElementById('cameraStream');

            async function startCamera() {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    alert('Camera API not supported in this browser.');
                    return;
                }
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
                    video.srcObject = stream;
                    cameraStream.style.display = '';
                    openBtn.style.display = 'none';
                } catch (e) {
                    alert('Could not access camera: ' + e.message);
                }
            }

            function stopCamera() {
                if (stream) {
                    stream.getTracks().forEach(t => t.stop());
                    stream = null;
                }
                cameraStream.style.display = 'none';
                openBtn.style.display = '';
                preview.style.display = 'none';
                retakeBtn.style.display = 'none';
                captureBtn.style.display = '';
                previewImg.src = '';
            }

            openBtn && openBtn.addEventListener('click', startCamera);

            captureBtn && captureBtn.addEventListener('click', function () {
                const width = video.videoWidth || 1280;
                const height = video.videoHeight || 720;
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, width, height);

                canvas.toBlob(function (blob) {
                    const file = new File([blob], 'camera.jpg', { type: blob.type || 'image/jpeg' });

                    // Robust upload: prefer global Livewire.upload (capital or lowercase), otherwise fallback to emitting base64 data
                    const lw = (window.Livewire || window.livewire);

                    if (lw && typeof lw.upload === 'function') {
                        lw.upload('picture_file', file,
                            () => {
                                // success: show preview, allow retake
                                previewImg.src = URL.createObjectURL(file);
                                preview.style.display = '';
                                retakeBtn.style.display = '';
                                captureBtn.style.display = 'none';
                                // stop camera if you want to conserve resources (optional)
                                // stopCamera();
                            },
                            (error) => {
                                alert('Upload failed: ' + error);
                            }
                        );
                    } else if (window.livewire && typeof window.livewire.emit === 'function') {
                        // Fallback: convert to base64 and emit an event; handle it in Livewire component (e.g. listen for 'pictureFileCaptured')
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            // emit base64 payload to Livewire; implement server-side handler to accept it
                            window.livewire.emit('pictureFileCaptured', e.target.result);
                            previewImg.src = URL.createObjectURL(file);
                            preview.style.display = '';
                            retakeBtn.style.display = '';
                            captureBtn.style.display = 'none';
                        };
                        reader.onerror = function() {
                            alert('Upload failed: unable to read file');
                        };
                        reader.readAsDataURL(file);
                    } else {
                        alert('Livewire file upload is not available in this environment.');
                    }
                }, 'image/jpeg', 0.95);
            });

            retakeBtn && retakeBtn.addEventListener('click', function () {
                preview.style.display = 'none';
                retakeBtn.style.display = 'none';
                captureBtn.style.display = '';
                previewImg.src = '';
            });

            closeBtn && closeBtn.addEventListener('click', stopCamera);

            window.addEventListener('beforeunload', function () {
                if (stream) stream.getTracks().forEach(t => t.stop());
            });
        });
    </script>
</div>

