/**
 * Configurator for Штори ПроФен
 * Handles interactive product configuration
 */

(function($) {
    'use strict';
    
    window.ShoriProfenConfigurator = {
        
        // Configuration
        config: {
            steps: 5,
            api: shoriprofen_ajax.ajax_url,
            nonce: shoriprofen_ajax.nonce,
            currency: 'UAH'
        },
        
        // Current state
        state: {
            currentStep: 1,
            selections: {},
            products: {},
            pricing: {},
            installationAvailable: false,
            totalPrice: 0
        },
        
        // Initialize configurator
        init: function() {
            this.loadProducts();
            this.setupEventListeners();
            this.initializeSteps();
            this.loadSavedConfiguration();
        },
        
        // Load products from API
        loadProducts: function() {
            var self = this;
            
            $.ajax({
                url: this.config.api,
                type: 'POST',
                data: {
                    action: 'load_configurator_products',
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.state.products = response.data.products;
                        self.state.pricing = response.data.pricing;
                        self.renderProducts();
                    }
                },
                error: function() {
                    console.error('Failed to load configurator products');
                }
            });
        },
        
        // Setup event listeners
        setupEventListeners: function() {
            var self = this;
            
            // Step navigation
            $('.configurator-nav button').on('click', function() {
                var step = $(this).data('step');
                self.goToStep(step);
            });
            
            // Product selection
            $(document).on('click', '.configurator-option', function() {
                var $this = $(this);
                var step = $this.closest('.configurator-step').data('step');
                var value = $this.data('value');
                
                self.selectOption(step, value, $this);
            });
            
            // Form submissions
            $('.configurator-form').on('submit', function(e) {
                e.preventDefault();
                self.saveConfiguration();
            });
            
            // Video instructions
            $('.video-instruction-btn').on('click', function() {
                self.showVideoInstruction($(this).data('video'));
            });
            
            // Installation toggle
            $('#installation-service').on('change', function() {
                self.toggleInstallationService($(this).is(':checked'));
            });
            
            // Summary updates
            $('.summary-item-remove').on('click', function() {
                var item = $(this).data('item');
                self.removeItem(item);
            });
        },
        
        // Initialize steps
        initializeSteps: function() {
            this.updateStepNavigation();
            this.updateProgressBar();
        },
        
        // Load saved configuration
        loadSavedConfiguration: function() {
            var saved = localStorage.getItem('configurator_selections');
            if (saved) {
                try {
                    this.state.selections = JSON.parse(saved);
                    this.restoreSelections();
                } catch (e) {
                    console.error('Failed to load saved configuration');
                }
            }
        },
        
        // Render products for each step
        renderProducts: function() {
            var self = this;
            
            // Step 1: Room type
            this.renderRoomTypes();
            
            // Step 2: Window type
            this.renderWindowTypes();
            
            // Step 3: Product type
            this.renderProductTypes();
            
            // Step 4: Material/Color
            this.renderMaterials();
            
            // Step 5: Additional options
            this.renderAdditionalOptions();
        },
        
        // Render room types
        renderRoomTypes: function() {
            var container = $('#step-room-types .configurator-options');
            var roomTypes = [
                { id: 'bedroom', name: 'Спальня', icon: '🛏️', description: 'Уютная атмосфера для отдыха' },
                { id: 'kitchen', name: 'Кухня', icon: '🍳', description: 'Практичность и стиль' },
                { id: 'living_room', name: 'Гостиная', icon: '🛋️', description: 'Центр семейной жизни' },
                { id: 'office', name: 'Офис', icon: '💼', description: 'Концентрация и продуктивность' },
                { id: 'kids_room', name: 'Детская', icon: '🧸', description: 'Безопасность и яркие цвета' },
                { id: 'attic', name: 'Мансарда', icon: '🏠', description: 'Уникальное пространство' },
                { id: 'balcony', name: 'Балкон', icon: '🌿', description: 'Расширение жилого пространства' }
            ];
            
            container.empty();
            roomTypes.forEach(function(room) {
                var option = self.createOption(room, 'room_type');
                container.append(option);
            });
        },
        
        // Render window types
        renderWindowTypes: function() {
            var container = $('#step-window-types .configurator-options');
            var windowTypes = [
                { id: 'standard', name: 'Стандартные', icon: '🪟', description: 'Классические прямоугольные окна' },
                { id: 'mansard', name: 'Мансардные', icon: '🏚️', description: 'Наклонные окна в чердаке' },
                { id: 'balcony', name: 'Балконные', icon: '🌺', description: 'Двери на балкон с окнами' },
                { id: 'arched', name: 'Арочные', icon: '🏛️', description: 'Элегантные арочные формы' },
                { id: 'trapezoid', name: 'Трапециевидные', icon: '🔺', description: 'Современная геометрия' }
            ];
            
            container.empty();
            windowTypes.forEach(function(window) {
                var option = self.createOption(window, 'window_type');
                container.append(option);
            });
        },
        
        // Render product types
        renderProductTypes: function() {
            var container = $('#step-product-types .configurator-options');
            var products = this.state.products;
            
            container.empty();
            Object.keys(products).forEach(function(productId) {
                var product = products[productId];
                var option = self.createProductOption(product);
                container.append(option);
            });
        },
        
        // Render materials
        renderMaterials: function() {
            var container = $('#step-materials .configurator-options');
            var materials = [
                { id: 'fabric_premium', name: 'Премиум ткань', price: '+1500', color: '#8B4513' },
                { id: 'fabric_eco', name: 'Эко-ткань', price: '+800', color: '#228B22' },
                { id: 'aluminum', name: 'Алюминий', price: '+1200', color: '#C0C0C0' },
                { id: 'wood', name: 'Дерево', price: '+2000', color: '#8B4513' },
                { id: 'plastic', name: 'Пластик', price: '+500', color: '#FFFFFF' }
            ];
            
            container.empty();
            materials.forEach(function(material) {
                var option = self.createMaterialOption(material);
                container.append(option);
            });
        },
        
        // Render additional options
        renderAdditionalOptions: function() {
            var container = $('#step-additional .configurator-options');
            var options = [
                { id: 'smart_control', name: 'Умное управление', price: '+2500', icon: '📱' },
                { id: 'remote_control', name: 'Пульт ДУ', price: '+800', icon: '🎮' },
                { id: 'timer', name: 'Таймер', price: '+500', icon: '⏰' },
                { id: 'sensor', name: 'Датчик света', price: '+1200', icon: '💡' },
                { id: 'child_safety', name: 'Детская безопасность', price: '+600', icon: '👶' }
            ];
            
            container.empty();
            options.forEach(function(option) {
                var optionEl = self.createAdditionalOption(option);
                container.append(optionEl);
            });
        },
        
        // Create option element
        createOption: function(data, type) {
            var option = $('<div class="configurator-option" data-value="' + data.id + '">');
            
            option.append('<div class="option-icon">' + data.icon + '</div>');
            option.append('<h4>' + data.name + '</h4>');
            option.append('<p>' + data.description + '</p>');
            
            return option;
        },
        
        // Create product option
        createProductOption: function(product) {
            var option = $('<div class="configurator-option product-option" data-value="' + product.id + '">');
            
            option.append('<div class="product-image"><img src="' + product.image + '" alt="' + product.name + '"></div>');
            option.append('<h4>' + product.name + '</h4>');
            option.append('<p class="product-price">' + this.formatPrice(product.price) + '</p>');
            option.append('<p class="product-description">' + product.description + '</p>');
            
            // Add AR button if available
            if (product.ar_ready) {
                option.append('<button class="ar-view-btn" data-product-id="' + product.id + '">🥽 AR</button>');
            }
            
            return option;
        },
        
        // Create material option
        createMaterialOption: function(material) {
            var option = $('<div class="configurator-option material-option" data-value="' + material.id + '">');
            
            option.append('<div class="material-color" style="background-color: ' + material.color + '"></div>');
            option.append('<h4>' + material.name + '</h4>');
            option.append('<p class="material-price">' + material.price + '</p>');
            
            return option;
        },
        
        // Create additional option
        createAdditionalOption: function(option) {
            var optionEl = $('<div class="configurator-option additional-option" data-value="' + option.id + '">');
            
            optionEl.append('<div class="option-icon">' + option.icon + '</div>');
            optionEl.append('<h4>' + option.name + '</h4>');
            optionEl.append('<p class="option-price">' + option.price + '</p>');
            
            return optionEl;
        },
        
        // Select option
        selectOption: function(step, value, $element) {
            // Remove selected from siblings
            $element.siblings().removeClass('selected');
            $element.addClass('selected');
            
            // Save selection
            this.state.selections['step_' + step] = value;
            this.saveSelections();
            
            // Update summary
            this.updateSummary();
            
            // Auto-advance to next step after delay
            setTimeout(function() {
                if (step < this.config.steps) {
                    this.goToStep(step + 1);
                }
            }.bind(this), 500);
        },
        
        // Go to specific step
        goToStep: function(step) {
            // Hide all steps
            $('.configurator-step').removeClass('active');
            
            // Show target step
            $('#step-' + step).addClass('active');
            
            // Update current step
            this.state.currentStep = step;
            
            // Update navigation
            this.updateStepNavigation();
            this.updateProgressBar();
            
            // Load step-specific content
            this.loadStepContent(step);
        },
        
        // Update step navigation
        updateStepNavigation: function() {
            $('.configurator-nav button').removeClass('active completed');
            
            for (var i = 1; i <= this.config.steps; i++) {
                var $btn = $('.configurator-nav button[data-step="' + i + '"]');
                
                if (i === this.state.currentStep) {
                    $btn.addClass('active');
                } else if (i < this.state.currentStep && this.state.selections['step_' + i]) {
                    $btn.addClass('completed');
                }
            }
        },
        
        // Update progress bar
        updateProgressBar: function() {
            var progress = (this.state.currentStep / this.config.steps) * 100;
            $('.configurator-progress-bar').css('width', progress + '%');
            $('.configurator-progress-text').text('Шаг ' + this.state.currentStep + ' из ' + this.config.steps);
        },
        
        // Load step-specific content
        loadStepContent: function(step) {
            switch (step) {
                case 3:
                    this.loadProductRecommendations();
                    break;
                case 4:
                    this.loadMaterialOptions();
                    break;
                case 5:
                    this.loadInstallationOptions();
                    break;
            }
        },
        
        // Load product recommendations
        loadProductRecommendations: function() {
            var roomType = this.state.selections.step_1;
            var windowType = this.state.selections.step_2;
            
            // Filter products based on selections
            var recommended = this.getRecommendedProducts(roomType, windowType);
            
            // Highlight recommended products
            $('.product-option').removeClass('recommended');
            recommended.forEach(function(productId) {
                $('.product-option[data-value="' + productId + '"]').addClass('recommended');
            });
        },
        
        // Get recommended products
        getRecommendedProducts: function(roomType, windowType) {
            var recommendations = {
                'bedroom_standard': ['plisse_premium', 'rolshtory_classic'],
                'kitchen_standard': ['zhalyuzi_aluminum', 'markizy_kitchen'],
                'living_room_standard': ['rolshtory_premium', 'plisse_eco'],
                'office_standard': ['zhalyuzi_wood', 'rolstavni_office'],
                'kids_room_standard': ['plisse_safe', 'zhalyuzi_plastic'],
                'attic_mansard': ['rolshtory_mansard', 'plisse_mansard'],
                'balcony_balcony': ['markizy_balcony', 'zhalyuzi_balcony']
            };
            
            var key = roomType + '_' + windowType;
            return recommendations[key] || [];
        },
        
        // Load material options
        loadMaterialOptions: function() {
            var productId = this.state.selections.step_3;
            var product = this.state.products[productId];
            
            if (product && product.materials) {
                // Filter materials based on product compatibility
                $('.material-option').removeClass('compatible incompatible');
                
                product.materials.forEach(function(materialId) {
                    $('.material-option[data-value="' + materialId + '"]').addClass('compatible');
                });
            }
        },
        
        // Load installation options
        loadInstallationOptions: function() {
            var selections = this.state.selections;
            var complexity = this.calculateInstallationComplexity(selections);
            
            // Show/hide installation option based on complexity
            if (complexity.requires_installation) {
                $('#installation-service').closest('.configurator-option').show();
                this.state.installationAvailable = true;
            } else {
                $('#installation-service').closest('.configurator-option').hide();
                this.state.installationAvailable = false;
            }
        },
        
        // Calculate installation complexity
        calculateInstallationComplexity: function(selections) {
            var productId = selections.step_3;
            var windowType = selections.step_2;
            var material = selections.step_4;
            
            var complexity = {
                requires_installation: false,
                base_price: 0,
                difficulty: 'easy'
            };
            
            // Check if professional installation is needed
            if (windowType === 'mansard' || windowType === 'arched') {
                complexity.requires_installation = true;
                complexity.difficulty = 'hard';
                complexity.base_price = 2500;
            } else if (material === 'wood' || material === 'aluminum') {
                complexity.requires_installation = true;
                complexity.difficulty = 'medium';
                complexity.base_price = 1500;
            }
            
            return complexity;
        },
        
        // Toggle installation service
        toggleInstallationService: function(enabled) {
            this.state.selections.installation_service = enabled;
            this.updateSummary();
        },
        
        // Update summary
        updateSummary: function() {
            var self = this;
            var summary = $('.configurator-summary');
            var items = summary.find('.summary-items');
            var total = summary.find('.summary-total');
            
            items.empty();
            var totalPrice = 0;
            
            // Add selected items to summary
            Object.keys(this.state.selections).forEach(function(key) {
                if (key.startsWith('step_')) {
                    var step = key.replace('step_', '');
                    var value = self.state.selections[key];
                    var item = self.getSummaryItem(step, value);
                    
                    if (item) {
                        items.append(item);
                        totalPrice += item.price;
                    }
                }
            });
            
            // Add installation service if selected
            if (this.state.selections.installation_service) {
                var installationPrice = this.calculateInstallationPrice();
                var installationItem = $('<div class="summary-item">');
                installationItem.append('<span>Монтаж под ключ</span>');
                installationItem.append('<span class="price">' + this.formatPrice(installationPrice) + '</span>');
                installationItem.append('<button class="summary-item-remove" data-item="installation_service">×</button>');
                items.append(installationItem);
                totalPrice += installationPrice;
            }
            
            // Update total
            this.state.totalPrice = totalPrice;
            total.find('.amount').text(this.formatPrice(totalPrice));
        },
        
        // Get summary item
        getSummaryItem: function(step, value) {
            var item = null;
            
            switch (step) {
                case '1': // Room type
                    var roomTypes = {
                        'bedroom': 'Спальня',
                        'kitchen': 'Кухня',
                        'living_room': 'Гостиная',
                        'office': 'Офис',
                        'kids_room': 'Детская',
                        'attic': 'Мансарда',
                        'balcony': 'Балкон'
                    };
                    item = { name: roomTypes[value], price: 0 };
                    break;
                    
                case '2': // Window type
                    var windowTypes = {
                        'standard': 'Стандартные окна',
                        'mansard': 'Мансардные окна',
                        'balcony': 'Балконные окна',
                        'arched': 'Арочные окна',
                        'trapezoid': 'Трапециевидные окна'
                    };
                    item = { name: windowTypes[value], price: 0 };
                    break;
                    
                case '3': // Product
                    var product = this.state.products[value];
                    if (product) {
                        item = { name: product.name, price: product.price };
                    }
                    break;
                    
                case '4': // Material
                    var materials = {
                        'fabric_premium': { name: 'Премиум ткань', price: 1500 },
                        'fabric_eco': { name: 'Эко-ткань', price: 800 },
                        'aluminum': { name: 'Алюминий', price: 1200 },
                        'wood': { name: 'Дерево', price: 2000 },
                        'plastic': { name: 'Пластик', price: 500 }
                    };
                    item = materials[value];
                    break;
                    
                case '5': // Additional options
                    var options = {
                        'smart_control': { name: 'Умное управление', price: 2500 },
                        'remote_control': { name: 'Пульт ДУ', price: 800 },
                        'timer': { name: 'Таймер', price: 500 },
                        'sensor': { name: 'Датчик света', price: 1200 },
                        'child_safety': { name: 'Детская безопасность', price: 600 }
                    };
                    item = options[value];
                    break;
            }
            
            if (item) {
                var itemEl = $('<div class="summary-item">');
                itemEl.append('<span>' + item.name + '</span>');
                itemEl.append('<span class="price">' + this.formatPrice(item.price) + '</span>');
                itemEl.append('<button class="summary-item-remove" data-item="step_' + step + '">×</button>');
                return itemEl;
            }
            
            return null;
        },
        
        // Calculate installation price
        calculateInstallationPrice: function() {
            var complexity = this.calculateInstallationComplexity(this.state.selections);
            var basePrice = complexity.base_price;
            
            // Add location-based pricing
            var location = this.getUserLocation();
            var locationMultiplier = this.getLocationMultiplier(location);
            
            return Math.round(basePrice * locationMultiplier);
        },
        
        // Get user location (simplified)
        getUserLocation: function() {
            // In real implementation, use geolocation or user input
            return 'kiev'; // Default to Kiev
        },
        
        // Get location multiplier
        getLocationMultiplier: function(location) {
            var multipliers = {
                'kiev': 1.0,
                'kharkiv': 0.9,
                'odesa': 0.95,
                'dnipro': 0.9,
                'lviv': 0.85
            };
            
            return multipliers[location] || 1.0;
        },
        
        // Remove item from summary
        removeItem: function(item) {
            delete this.state.selections[item];
            this.saveSelections();
            this.updateSummary();
            
            // Go back to relevant step
            if (item.startsWith('step_')) {
                var step = item.replace('step_', '');
                this.goToStep(parseInt(step));
            }
        },
        
        // Save selections
        saveSelections: function() {
            localStorage.setItem('configurator_selections', JSON.stringify(this.state.selections));
        },
        
        // Restore selections
        restoreSelections: function() {
            var self = this;
            
            Object.keys(this.state.selections).forEach(function(key) {
                var value = self.state.selections[key];
                
                if (key.startsWith('step_')) {
                    var step = key.replace('step_', '');
                    $('.configurator-step[data-step="' + step + '"] .configurator-option[data-value="' + value + '"]').addClass('selected');
                }
            });
            
            this.updateSummary();
        },
        
        // Save configuration
        saveConfiguration: function() {
            var self = this;
            
            $.ajax({
                url: this.config.api,
                type: 'POST',
                data: {
                    action: 'save_configuration',
                    selections: this.state.selections,
                    total_price: this.state.totalPrice,
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        ShoriProfen.showNotification('Конфигурация сохранена!', 'success');
                        
                        // Redirect to checkout or thank you page
                        if (response.data.redirect) {
                            window.location.href = response.data.redirect;
                        }
                    } else {
                        ShoriProfen.showNotification('Ошибка сохранения', 'error');
                    }
                },
                error: function() {
                    ShoriProfen.showNotification('Ошибка сервера', 'error');
                }
            });
        },
        
        // Show video instruction
        showVideoInstruction: function(videoId) {
            var modal = $('<div class="video-modal">');
            var content = $('<div class="video-content">');
            
            content.append('<button class="video-close">×</button>');
            content.append('<div class="video-wrapper">');
            content.append('<iframe src="https://www.youtube.com/embed/' + videoId + '" frameborder="0" allowfullscreen></iframe>');
            content.append('</div>');
            
            modal.append(content);
            $('body').append(modal);
            
            setTimeout(function() {
                modal.addClass('active');
            }, 10);
            
            // Close handlers
            $('.video-close, .video-modal').on('click', function(e) {
                if (e.target === this || $(e.target).hasClass('video-close')) {
                    modal.removeClass('active');
                    setTimeout(function() {
                        modal.remove();
                    }, 300);
                }
            });
        },
        
        // Format price
        formatPrice: function(price) {
            return new Intl.NumberFormat('uk-UA', {
                style: 'currency',
                currency: this.config.currency,
                minimumFractionDigits: 0
            }).format(price);
        }
    };
    
    // Initialize configurator when DOM is ready
    $(document).ready(function() {
        if ($('#configurator').length) {
            ShoriProfenConfigurator.init();
        }
    });
    
})(jQuery);
