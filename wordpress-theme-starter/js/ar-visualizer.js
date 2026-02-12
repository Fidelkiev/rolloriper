/**
 * AR Visualizer for Штори ПроФен
 * Handles AR functionality and 2D fallback
 */

(function() {
    'use strict';
    
    window.ShoriProfenAR = {
        
        // Configuration
        config: {
            supportedDevices: {
                iOS: ['iPhone', 'iPad'],
                Android: ['Android']
            },
            arLibraries: {
                webxr: 'WebXR',
                arjs: 'AR.js',
                aframe: 'A-Frame'
            }
        },
        
        // Current state
        state: {
            isSupported: false,
            currentLibrary: null,
            activeSession: null,
            productModels: {},
            fallbackMode: false
        },
        
        // Initialize AR system
        init: function() {
            this.detectSupport();
            this.loadProductModels();
            this.setupEventListeners();
        },
        
        // Detect AR support
        detectSupport: function() {
            var self = this;
            
            // Check WebXR support
            if ('xr' in navigator) {
                navigator.xr.isSessionSupported('immersive-ar').then(function(supported) {
                    if (supported) {
                        self.state.isSupported = true;
                        self.state.currentLibrary = 'webxr';
                        console.log('WebXR AR supported');
                    } else {
                        self.checkAlternativeLibraries();
                    }
                }).catch(function() {
                    self.checkAlternativeLibraries();
                });
            } else {
                this.checkAlternativeLibraries();
            }
        },
        
        // Check alternative AR libraries
        checkAlternativeLibraries: function() {
            // Check for AR.js support
            if (this.checkARJSSupport()) {
                this.state.isSupported = true;
                this.state.currentLibrary = 'arjs';
                console.log('AR.js supported');
                return;
            }
            
            // Check for A-Frame support
            if (this.checkAFrameSupport()) {
                this.state.isSupported = true;
                this.state.currentLibrary = 'aframe';
                console.log('A-Frame supported');
                return;
            }
            
            // No AR support - use fallback
            console.log('AR not supported, using 2D fallback');
            this.state.fallbackMode = true;
        },
        
        // Check AR.js support
        checkARJSSupport: function() {
            // Check for WebRTC and getUserMedia support
            return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
        },
        
        // Check A-Frame support
        checkAFrameSupport: function() {
            // Check for WebGL support
            try {
                var canvas = document.createElement('canvas');
                return !!(window.WebGLRenderingContext && 
                         (canvas.getContext('webgl') || canvas.getContext('experimental-webgl')));
            } catch (e) {
                return false;
            }
        },
        
        // Load product models
        loadProductModels: function() {
            var self = this;
            
            // Load models from API or local storage
            $.ajax({
                url: shoriprofen_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'load_ar_models',
                    nonce: shoriprofen_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.state.productModels = response.data;
                        console.log('AR models loaded:', Object.keys(self.state.productModels).length);
                    }
                },
                error: function() {
                    console.error('Failed to load AR models');
                }
            });
        },
        
        // Setup event listeners
        setupEventListeners: function() {
            var self = this;
            
            // AR button clicks
            $(document).on('click', '.ar-view-btn', function(e) {
                e.preventDefault();
                var productId = $(this).data('product-id');
                self.startAR(productId);
            });
            
            // Close AR session
            $(document).on('click', '.ar-close-btn', function() {
                self.endARSession();
            });
            
            // Handle device orientation changes
            window.addEventListener('deviceorientation', this.handleOrientationChange);
        },
        
        // Start AR experience
        startAR: function(productId) {
            console.log('Starting AR for product:', productId);
            
            if (!this.state.isSupported || this.state.fallbackMode) {
                this.show2DFallback(productId);
                return;
            }
            
            var model = this.state.productModels[productId];
            if (!model) {
                console.error('Model not found for product:', productId);
                this.show2DFallback(productId);
                return;
            }
            
            // Start AR based on available library
            switch (this.state.currentLibrary) {
                case 'webxr':
                    this.startWebXR(productId, model);
                    break;
                case 'arjs':
                    this.startARJS(productId, model);
                    break;
                case 'aframe':
                    this.startAFrame(productId, model);
                    break;
                default:
                    this.show2DFallback(productId);
            }
        },
        
        // Start WebXR session
        startWebXR: function(productId, model) {
            var self = this;
            
            navigator.xr.requestSession('immersive-ar', {
                requiredFeatures: ['local', 'hit-test'],
                optionalFeatures: ['dom-overlay']
            }).then(function(session) {
                self.state.activeSession = session;
                
                // Setup render loop
                session.requestAnimationFrame(self.onXRFrame.bind(self));
                
                // Setup session end handler
                session.addEventListener('end', self.onXRSessionEnd.bind(self));
                
                // Show AR interface
                self.showARInterface(productId, model, 'webxr');
                
                console.log('WebXR session started');
            }).catch(function(error) {
                console.error('Failed to start WebXR session:', error);
                self.show2DFallback(productId);
            });
        },
        
        // Start AR.js session
        startARJS: function(productId, model) {
            var self = this;
            
            // Request camera access
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(stream) {
                    self.state.activeSession = stream;
                    self.showARInterface(productId, model, 'arjs');
                    console.log('AR.js session started');
                })
                .catch(function(error) {
                    console.error('Failed to access camera:', error);
                    self.show2DFallback(productId);
                });
        },
        
        // Start A-Frame session
        startAFrame: function(productId, model) {
            var self = this;
            
            // Load A-Frame if not already loaded
            if (typeof AFRAME === 'undefined') {
                this.loadAFrame(function() {
                    self.showARInterface(productId, model, 'aframe');
                });
            } else {
                this.showARInterface(productId, model, 'aframe');
            }
        },
        
        // Load A-Frame library
        loadAFrame: function(callback) {
            var script = document.createElement('script');
            script.src = 'https://aframe.io/releases/1.4.0/aframe.min.js';
            script.onload = callback;
            document.head.appendChild(script);
        },
        
        // Show AR interface
        showARInterface: function(productId, model, library) {
            var self = this;
            
            // Create AR overlay
            var overlay = $('<div class="ar-overlay">');
            overlay.attr('data-library', library);
            
            // Create AR container
            var container = $('<div class="ar-container">');
            
            // Add library-specific content
            switch (library) {
                case 'webxr':
                    container.append(this.createWebXRContent(model));
                    break;
                case 'arjs':
                    container.append(this.createARJSContent(model));
                    break;
                case 'aframe':
                    container.append(this.createAFrameContent(model));
                    break;
            }
            
            // Add controls
            var controls = $('<div class="ar-controls">');
            controls.append('<button class="ar-close-btn">✕ Закрыть</button>');
            controls.append('<button class="ar-screenshot-btn">📷 Снимок</button>');
            controls.append('<button class="ar-info-btn">ℹ️ Информация</button>');
            
            overlay.append(container);
            overlay.append(controls);
            
            $('body').append(overlay);
            
            // Animate in
            setTimeout(function() {
                overlay.addClass('active');
            }, 10);
            
            // Initialize library-specific functionality
            this.initializeLibrary(library, model);
        },
        
        // Create WebXR content
        createWebXRContent: function(model) {
            var content = $('<div class="webxr-content">');
            content.append('<canvas id="ar-canvas" width="640" height="480"></canvas>');
            return content;
        },
        
        // Create AR.js content
        createARJSContent: function(model) {
            var content = $('<div class="arjs-content">');
            content.append('<video id="ar-video" autoplay playsinline></video>');
            content.append('<div id="ar-marker"></div>');
            return content;
        },
        
        // Create A-Frame content
        createAFrameContent: function(model) {
            var content = $('<div class="aframe-content">');
            content.append('<a-scene embedded arjs="sourceType: webcam; debugUIEnabled: false;">');
            content.append('<a-marker preset="hiro">');
            content.append('<a-entity position="0 0.5 0" scale="0.5 0.5 0.5" gltf-model="#product-model"></a-entity>');
            content.append('</a-marker>');
            content.append('<a-entity camera></a-entity>');
            content.append('</a-scene>');
            content.append('<a-asset-item id="product-model" src="' + model.url + '"></a-asset-item>');
            return content;
        },
        
        // Initialize library-specific functionality
        initializeLibrary: function(library, model) {
            switch (library) {
                case 'webxr':
                    this.initializeWebXR(model);
                    break;
                case 'arjs':
                    this.initializeARJS(model);
                    break;
                case 'aframe':
                    this.initializeAFrame(model);
                    break;
            }
        },
        
        // Initialize WebXR
        initializeWebXR: function(model) {
            // WebXR initialization logic
            console.log('Initializing WebXR with model:', model);
        },
        
        // Initialize AR.js
        initializeARJS: function(model) {
            var self = this;
            var video = document.getElementById('ar-video');
            
            if (this.state.activeSession) {
                video.srcObject = this.state.activeSession;
                
                // Initialize AR.js
                if (typeof THREEx === 'undefined') {
                    this.loadARJS(function() {
                        self.setupARJSScene(model);
                    });
                } else {
                    this.setupARJSScene(model);
                }
            }
        },
        
        // Load AR.js library
        loadARJS: function(callback) {
            var scripts = [
                'https://aframe.io/releases/1.4.0/aframe.min.js',
                'https://raw.githack.com/AR-js-org/AR.js/master/aframe/build/aframe-ar.js'
            ];
            
            var loaded = 0;
            scripts.forEach(function(src) {
                var script = document.createElement('script');
                script.src = src;
                script.onload = function() {
                    loaded++;
                    if (loaded === scripts.length) {
                        callback();
                    }
                };
                document.head.appendChild(script);
            });
        },
        
        // Setup AR.js scene
        setupARJSScene: function(model) {
            // AR.js scene setup logic
            console.log('Setting up AR.js scene with model:', model);
        },
        
        // Initialize A-Frame
        initializeAFrame: function(model) {
            // A-Frame initialization logic
            console.log('Initializing A-Frame with model:', model);
        },
        
        // Show 2D fallback
        show2DFallback: function(productId) {
            console.log('Showing 2D fallback for product:', productId);
            
            var model = this.state.productModels[productId];
            if (!model) {
                ShoriProfen.showNotification('Модель не найдена', 'error');
                return;
            }
            
            // Create 2D preview modal
            var modal = $('<div class="ar-2d-modal">');
            var content = $('<div class="ar-2d-content">');
            
            // Add 2D preview
            var preview = $('<div class="ar-2d-preview">');
            if (model.preview_image) {
                preview.append('<img src="' + model.preview_image + '" alt="' + model.name + '">');
            }
            
            // Add controls
            var controls = $('<div class="ar-2d-controls">');
            controls.append('<button class="ar-2d-rotate">↻ Повернуть</button>');
            controls.append('<button class="ar-2d-zoom">🔍 Увеличить</button>');
            controls.append('<button class="ar-2d-close">✕ Закрыть</button>');
            
            // Add information
            var info = $('<div class="ar-2d-info">');
            info.append('<h3>' + model.name + '</h3>');
            info.append('<p>' + model.description + '</p>');
            info.append('<div class="ar-2d-features">');
            model.features.forEach(function(feature) {
                info.append('<span class="feature">' + feature + '</span>');
            });
            info.append('</div>');
            
            content.append(preview);
            content.append(controls);
            content.append(info);
            modal.append(content);
            
            $('body').append(modal);
            
            // Animate in
            setTimeout(function() {
                modal.addClass('active');
            }, 10);
            
            // Setup 2D controls
            this.setup2DControls(productId, model);
        },
        
        // Setup 2D controls
        setup2DControls: function(productId, model) {
            var self = this;
            var preview = $('.ar-2d-preview img');
            var currentRotation = 0;
            var currentZoom = 1;
            
            // Rotate button
            $('.ar-2d-rotate').on('click', function() {
                currentRotation += 90;
                preview.css('transform', 'rotate(' + currentRotation + 'deg) scale(' + currentZoom + ')');
            });
            
            // Zoom button
            $('.ar-2d-zoom').on('click', function() {
                currentZoom = currentZoom >= 2 ? 1 : currentZoom + 0.5;
                preview.css('transform', 'rotate(' + currentRotation + 'deg) scale(' + currentZoom + ')');
            });
            
            // Close button
            $('.ar-2d-close').on('click', function() {
                $('.ar-2d-modal').removeClass('active');
                setTimeout(function() {
                    $('.ar-2d-modal').remove();
                }, 300);
            });
            
            // Close on background click
            $('.ar-2d-modal').on('click', function(e) {
                if (e.target === this) {
                    $(this).removeClass('active');
                    setTimeout(function() {
                        $(this).remove();
                    }, 300);
                }
            });
        },
        
        // End AR session
        endARSession: function() {
            if (this.state.activeSession) {
                switch (this.state.currentLibrary) {
                    case 'webxr':
                        if (this.state.activeSession.end) {
                            this.state.activeSession.end();
                        }
                        break;
                    case 'arjs':
                        if (this.state.activeSession.getTracks) {
                            this.state.activeSession.getTracks().forEach(function(track) {
                                track.stop();
                            });
                        }
                        break;
                }
                
                this.state.activeSession = null;
            }
            
            // Remove AR overlay
            $('.ar-overlay').removeClass('active');
            setTimeout(function() {
                $('.ar-overlay').remove();
            }, 300);
        },
        
        // Handle XR frame
        onXRFrame: function(timestamp, frame) {
            // WebXR render loop
            if (this.state.activeSession) {
                this.state.activeSession.requestAnimationFrame(this.onXRFrame.bind(this));
                // Render frame logic here
            }
        },
        
        // Handle XR session end
        onXRSessionEnd: function() {
            console.log('WebXR session ended');
            this.state.activeSession = null;
            this.endARSession();
        },
        
        // Handle device orientation change
        handleOrientationChange: function(event) {
            // Handle device orientation for AR
            if (this.state.activeSession) {
                // Update AR view based on orientation
            }
        },
        
        // Take screenshot
        takeScreenshot: function() {
            // Screenshot functionality
            var canvas = document.getElementById('ar-canvas');
            if (canvas) {
                var dataUrl = canvas.toDataURL('image/png');
                this.downloadScreenshot(dataUrl);
            }
        },
        
        // Download screenshot
        downloadScreenshot: function(dataUrl) {
            var link = document.createElement('a');
            link.download = 'shoriprofen-ar-screenshot.png';
            link.href = dataUrl;
            link.click();
        }
    };
    
    // Initialize AR when DOM is ready
    $(document).ready(function() {
        ShoriProfenAR.init();
    });
    
})();
