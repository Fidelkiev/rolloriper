/**
 * Main JavaScript for Штори ПроФен theme
 * Handles core functionality and initialization
 */

(function($) {
    'use strict';
    
    // Main application object
    window.ShoriProfen = {
        
        // Configuration
        config: {
            api: shoriprofen_ajax.ajax_url,
            nonce: shoriprofen_ajax.nonce,
            siteUrl: shoriprofen_ajax.site_url,
            breakpoints: {
                mobile: 768,
                tablet: 1024,
                desktop: 1200
            }
        },
        
        // Initialize everything
        init: function() {
            this.setupEventListeners();
            this.initializeComponents();
            this.setupARSupport();
            this.setupLazyLoading();
        },
        
        // Setup event listeners
        setupEventListeners: function() {
            // Mobile menu toggle
            $('.mobile-menu-toggle').on('click', function(e) {
                e.preventDefault();
                $('body').toggleClass('mobile-menu-open');
            });
            
            // Smooth scroll for anchor links
            $('a[href^="#"]').on('click', function(e) {
                var target = $(this.getAttribute('href'));
                if (target.length) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 800);
                }
            });
            
            // Form submissions
            $('.shoriprofen-form').on('submit', this.handleFormSubmit);
            
            // AR button clicks
            $('.ar-view-btn').on('click', this.handleARView);
            
            // Filter interactions
            $('.filter-group input, .filter-group select').on('change', this.handleFilterChange);
        },
        
        // Initialize components
        initializeComponents: function() {
            // Initialize designfinder
            if ($('#designfinder').length) {
                this.initializeDesignfinder();
            }
            
            // Initialize configurator
            if ($('#configurator').length) {
                this.initializeConfigurator();
            }
            
            // Initialize gallery
            if ($('.visualization-gallery').length) {
                this.initializeGallery();
            }
            
            // Initialize tooltips
            this.initializeTooltips();
        },
        
        // Designfinder initialization
        initializeDesignfinder: function() {
            var self = this;
            
            // Filter functionality
            $('#designfinder-filters').on('change', function() {
                self.filterVisualizations();
            });
            
            // Load initial visualizations
            this.loadVisualizations();
        },
        
        // Filter visualizations
        filterVisualizations: function() {
            var filters = {
                room_type: $('#filter-room-type').val(),
                interior_style: $('#filter-style').val(),
                window_type: $('#filter-window-type').val(),
                visual_type: $('#filter-visual-type').val()
            };
            
            // Remove empty filters
            Object.keys(filters).forEach(function(key) {
                if (!filters[key]) {
                    delete filters[key];
                }
            });
            
            this.loadVisualizations(filters);
        },
        
        // Load visualizations via AJAX
        loadVisualizations: function(filters = {}) {
            var self = this;
            
            $.ajax({
                url: this.config.api,
                type: 'POST',
                data: {
                    action: 'load_visualizations',
                    filters: filters,
                    nonce: this.config.nonce
                },
                beforeSend: function() {
                    $('#visualizations-grid').addClass('loading');
                },
                success: function(response) {
                    if (response.success) {
                        self.renderVisualizations(response.data);
                    } else {
                        console.error('Error loading visualizations:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                },
                complete: function() {
                    $('#visualizations-grid').removeClass('loading');
                }
            });
        },
        
        // Render visualizations
        renderVisualizations: function(visualizations) {
            var grid = $('#visualizations-grid');
            grid.empty();
            
            if (visualizations.length === 0) {
                grid.html('<div class="no-results">Визуализации не найдены</div>');
                return;
            }
            
            visualizations.forEach(function(vis) {
                var card = self.createVisualizationCard(vis);
                grid.append(card);
            });
            
            // Initialize lazy loading for new images
            this.setupLazyLoading();
        },
        
        // Create visualization card
        createVisualizationCard: function(vis) {
            var card = $('<div class="visualization-card">');
            
            // Image
            var image = $('<div class="visualization-image">');
            if (vis.image) {
                image.append('<img src="' + vis.image + '" alt="' + vis.title + '" loading="lazy">');
            }
            
            // AR button if available
            if (vis.ar_ready) {
                image.append('<button class="ar-view-btn" data-product-id="' + vis.id + '">🥽 AR</button>');
            }
            
            // Content
            var content = $('<div class="visualization-content">');
            content.append('<h3>' + vis.title + '</h3>');
            
            // Tags
            if (vis.tags && vis.tags.length > 0) {
                var tags = $('<div class="visualization-tags">');
                vis.tags.forEach(function(tag) {
                    tags.append('<span class="tag">' + tag + '</span>');
                });
                content.append(tags);
            }
            
            // Actions
            var actions = $('<div class="visualization-actions">');
            actions.append('<button class="btn btn-primary view-details" data-id="' + vis.id + '">Подробнее</button>');
            actions.append('<button class="btn btn-secondary save-visualization" data-id="' + vis.id + '">Сохранить</button>');
            content.append(actions);
            
            card.append(image);
            card.append(content);
            
            return card;
        },
        
        // Configurator initialization
        initializeConfigurator: function() {
            var self = this;
            
            // Step navigation
            $('.configurator-step-nav button').on('click', function() {
                var step = $(this).data('step');
                self.showConfiguratorStep(step);
            });
            
            // Option selection
            $('.configurator-option').on('click', function() {
                var $this = $(this);
                var step = $this.closest('.configurator-step').data('step');
                
                // Remove selected from siblings
                $this.siblings().removeClass('selected');
                $this.addClass('selected');
                
                // Save selection
                self.saveConfiguratorSelection(step, $this.data('value'));
                
                // Auto-advance to next step
                setTimeout(function() {
                    self.showConfiguratorStep(step + 1);
                }, 500);
            });
        },
        
        // Show configurator step
        showConfiguratorStep: function(step) {
            $('.configurator-step').removeClass('active');
            $('.configurator-step[data-step="' + step + '"]').addClass('active');
            
            // Update navigation
            $('.configurator-step-nav button').removeClass('active');
            $('.configurator-step-nav button[data-step="' + step + '"]').addClass('active');
            
            // Update progress
            this.updateConfiguratorProgress(step);
        },
        
        // Update configurator progress
        updateConfiguratorProgress: function(currentStep) {
            var totalSteps = $('.configurator-step').length;
            var progress = (currentStep / totalSteps) * 100;
            
            $('.configurator-progress-bar').css('width', progress + '%');
            $('.configurator-progress-text').text(currentStep + ' из ' + totalSteps);
        },
        
        // Save configurator selection
        saveConfiguratorSelection: function(step, value) {
            var selections = JSON.parse(localStorage.getItem('configurator_selections') || '{}');
            selections[step] = value;
            localStorage.setItem('configurator_selections', JSON.stringify(selections));
        },
        
        // Gallery initialization
        initializeGallery: function() {
            // Lightbox functionality
            $('.gallery-item').on('click', function() {
                var $this = $(this);
                var images = $this.closest('.visualization-gallery').find('.gallery-item').map(function() {
                    return {
                        src: $(this).find('img').attr('src'),
                        title: $(this).find('img').attr('alt')
                    };
                }).get();
                
                var currentIndex = $this.index();
                this.openLightbox(images, currentIndex);
            }.bind(this));
        },
        
        // Open lightbox
        openLightbox: function(images, startIndex) {
            // Simple lightbox implementation
            var lightbox = $('<div class="lightbox">');
            var content = $('<div class="lightbox-content">');
            var image = $('<img src="' + images[startIndex].src + '" alt="' + images[startIndex].title + '">');
            var close = $('<button class="lightbox-close">&times;</button>');
            
            content.append(image);
            content.append(close);
            lightbox.append(content);
            
            $('body').append(lightbox);
            
            // Close handlers
            close.on('click', function() {
                lightbox.remove();
            });
            
            lightbox.on('click', function(e) {
                if (e.target === lightbox[0]) {
                    lightbox.remove();
                }
            });
        },
        
        // Initialize tooltips
        initializeTooltips: function() {
            $('[data-tooltip]').each(function() {
                var $this = $(this);
                var tooltip = $('<div class="tooltip">' + $this.data('tooltip') + '</div>');
                
                $this.on('mouseenter', function() {
                    $('body').append(tooltip);
                    
                    var position = $this.offset();
                    tooltip.css({
                        top: position.top - tooltip.outerHeight() - 10,
                        left: position.left + ($this.outerWidth() / 2) - (tooltip.outerWidth() / 2)
                    });
                    
                    setTimeout(function() {
                        tooltip.addClass('visible');
                    }, 10);
                });
                
                $this.on('mouseleave', function() {
                    tooltip.removeClass('visible');
                    setTimeout(function() {
                        tooltip.remove();
                    }, 300);
                });
            });
        },
        
        // Setup AR support detection
        setupARSupport: function() {
            // Check if WebXR is supported
            if ('xr' in navigator) {
                navigator.xr.isSessionSupported('immersive-ar').then(function(supported) {
                    if (supported) {
                        $('body').addClass('ar-supported');
                    } else {
                        $('body').addClass('ar-not-supported');
                    }
                });
            } else {
                $('body').addClass('ar-not-supported');
            }
        },
        
        // Handle AR view
        handleARView: function(e) {
            e.preventDefault();
            var productId = $(e.target).data('product-id');
            
            if ($('body').hasClass('ar-supported')) {
                // Launch AR experience
                ShoriProfenAR.startAR(productId);
            } else {
                // Show 2D fallback
                ShoriProfenAR.show2DFallback(productId);
            }
        },
        
        // Handle form submissions
        handleFormSubmit: function(e) {
            e.preventDefault();
            var $form = $(e.target);
            var data = $form.serialize();
            
            $.ajax({
                url: ShoriProfen.config.api,
                type: 'POST',
                data: data + '&action=' + $form.data('action') + '&nonce=' + ShoriProfen.config.nonce,
                beforeSend: function() {
                    $form.find('button[type="submit"]').prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        ShoriProfen.showNotification(response.data.message, 'success');
                        if (response.data.redirect) {
                            window.location.href = response.data.redirect;
                        }
                    } else {
                        ShoriProfen.showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    ShoriProfen.showNotification('Произошла ошибка. Попробуйте еще раз.', 'error');
                },
                complete: function() {
                    $form.find('button[type="submit"]').prop('disabled', false);
                }
            });
        },
        
        // Handle filter changes
        handleFilterChange: function() {
            // Debounce filter changes
            clearTimeout(ShoriProfen.filterTimeout);
            ShoriProfen.filterTimeout = setTimeout(function() {
                ShoriProfen.filterVisualizations();
            }, 300);
        },
        
        // Setup lazy loading
        setupLazyLoading: function() {
            if ('IntersectionObserver' in window) {
                var imageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            var img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            observer.unobserve(img);
                        }
                    });
                });
                
                document.querySelectorAll('img[data-src]').forEach(function(img) {
                    imageObserver.observe(img);
                });
            }
        },
        
        // Show notification
        showNotification: function(message, type = 'info') {
            var notification = $('<div class="notification notification-' + type + '">');
            notification.text(message);
            
            $('body').append(notification);
            
            setTimeout(function() {
                notification.addClass('visible');
            }, 10);
            
            setTimeout(function() {
                notification.removeClass('visible');
                setTimeout(function() {
                    notification.remove();
                }, 300);
            }, 3000);
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        ShoriProfen.init();
    });
    
    // Initialize AR functionality
    window.ShoriProfenAR = {
        startAR: function(productId) {
            console.log('Starting AR for product:', productId);
            // AR implementation will be added separately
        },
        
        show2DFallback: function(productId) {
            console.log('Showing 2D fallback for product:', productId);
            // 2D fallback implementation
        }
    };
    
})(jQuery);
