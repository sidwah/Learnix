/**
 * Learnix Badge Display
 * 
 * This module handles displaying badges in the user interface including
 * rendering, animations, tooltips, and sharing functionality.
 */

class BadgeDisplay {
    /**
     * Initialize the badge display
     * @param {Object} options - Configuration options
     */
    constructor(options = {}) {
        this.containerSelector = options.containerSelector || '.badge-container';
        this.badgeSize = options.badgeSize || { width: 150, height: 150 };
        this.animationEnabled = options.animationEnabled !== false;
        this.tooltipsEnabled = options.tooltipsEnabled !== false;
        this.generator = options.generator || new BadgeGenerator();
        
        // Initialize tooltips if enabled
        if (this.tooltipsEnabled) {
            this.initTooltips();
        }
    }
    
    /**
     * Load and display user badges
     * @param {string|Element} container - Container element or selector
     * @param {Array} badges - Array of badge data objects
     * @param {Object} options - Display options
     */
    displayUserBadges(container, badges, options = {}) {
        const containerElement = typeof container === 'string' 
            ? document.querySelector(container) 
            : container;
            
        if (!containerElement) {
            console.error('Badge container element not found:', container);
            return;
        }
        
        // Clear container if specified
        if (options.clear !== false) {
            containerElement.innerHTML = '';
        }
        
        // Create badge elements
        badges.forEach(badge => {
            this.createBadgeElement(containerElement, badge, options);
        });
    }
    
    /**
     * Creates and adds a badge element to the container
     * @param {Element} container - Container element
     * @param {Object} badge - Badge data
     * @param {Object} options - Display options
     */
    createBadgeElement(container, badge, options = {}) {
        // Create wrapper element
        const wrapper = document.createElement('div');
        wrapper.className = 'badge-wrapper';
        
        if (options.wrapperClass) {
            wrapper.classList.add(options.wrapperClass);
        }
        
        // Create image element
        const img = document.createElement('img');
        img.className = 'badge-image';
        img.alt = badge.title || 'Achievement Badge';
        img.width = options.width || this.badgeSize.width;
        img.height = options.height || this.badgeSize.height;
        
        // Handle badge loading
        if (badge.svgContent) {
            // If SVG content is already provided
            img.src = this.generator.createDataUrl(badge.svgContent);
            this.setupBadgeElement(wrapper, img, badge, options);
        } else if (badge.imageUrl) {
            // If image URL is already provided
            img.src = badge.imageUrl;
            this.setupBadgeElement(wrapper, img, badge, options);
        } else {
            // Generate badge SVG on the fly
            this.generator.generateBadge(
                badge.achievementType, 
                badge.tierLevel,
                badge.userData
            ).then(svgString => {
                img.src = this.generator.createDataUrl(svgString);
                this.setupBadgeElement(wrapper, img, badge, options);
            }).catch(error => {
                console.error('Error generating badge:', error);
                img.src = '/assets/img/badges/fallback.svg';
                this.setupBadgeElement(wrapper, img, badge, options);
            });
        }
        
        // Add to container
        container.appendChild(wrapper);
    }
    
    /**
     * Sets up a badge element with event listeners and animations
     * @param {Element} wrapper - Badge wrapper element
     * @param {Element} img - Badge image element
     * @param {Object} badge - Badge data
     * @param {Object} options - Display options
     */
    setupBadgeElement(wrapper, img, badge, options) {
        wrapper.appendChild(img);
        
        // Add tooltip if enabled
        if (this.tooltipsEnabled && badge.title) {
            this.addTooltip(wrapper, badge);
        }
        
        // Add click handler if provided
        if (options.onClick) {
            wrapper.style.cursor = 'pointer';
            wrapper.addEventListener('click', (e) => {
                options.onClick(badge, e);
            });
        }
        
        // Add share button if enabled
        if (options.showShare !== false && badge.shareEnabled !== false) {
            this.addShareButton(wrapper, badge);
        }
        
        // Apply animation if enabled
        if (this.animationEnabled && options.animate !== false) {
            this.animateBadgeEntry(wrapper);
        }
    }
    
    /**
     * Initializes tooltip functionality
     */
    initTooltips() {
        // Simple tooltip styles
        if (!document.getElementById('badge-tooltip-styles')) {
            const style = document.createElement('style');
            style.id = 'badge-tooltip-styles';
            style.textContent = `
                .badge-tooltip {
                    position: absolute;
                    background: rgba(0, 0, 0, 0.8);
                    color: white;
                    padding: 8px 12px;
                    border-radius: 4px;
                    font-size: 14px;
                    z-index: 1000;
                    max-width: 250px;
                    transform: translateY(10px);
                    opacity: 0;
                    transition: opacity 0.3s, transform 0.3s;
                    pointer-events: none;
                }
                .badge-wrapper {
                    position: relative;
                    display: inline-block;
                    margin: 10px;
                }
                .badge-wrapper:hover .badge-tooltip {
                    opacity: 1;
                    transform: translateY(0);
                }
                .badge-share-btn {
                    position: absolute;
                    bottom: 5px;
                    right: 5px;
                    background: rgba(0, 0, 0, 0.6);
                    color: white;
                    border: none;
                    border-radius: 50%;
                    width: 30px;
                    height: 30px;
                    font-size: 16px;
                    cursor: pointer;
                    opacity: 0;
                    transition: opacity 0.3s;
                }
                .badge-wrapper:hover .badge-share-btn {
                    opacity: 1;
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    /**
     * Adds a tooltip to a badge element
     * @param {Element} wrapper - Badge wrapper element
     * @param {Object} badge - Badge data
     */
    addTooltip(wrapper, badge) {
        const tooltip = document.createElement('div');
        tooltip.className = 'badge-tooltip';
        
        // Build tooltip content
        let content = `<strong>${badge.title || 'Achievement'}</strong>`;
        
        if (badge.description) {
            content += `<br>${badge.description}`;
        }
        
        if (badge.dateEarned) {
            const date = new Date(badge.dateEarned);
            content += `<br><small>Earned: ${date.toLocaleDateString()}</small>`;
        }
        
        tooltip.innerHTML = content;
        wrapper.appendChild(tooltip);
    }
    
    /**
     * Adds a share button to a badge
     * @param {Element} wrapper - Badge wrapper element
     * @param {Object} badge - Badge data
     */
    addShareButton(wrapper, badge) {
        const shareBtn = document.createElement('button');
        shareBtn.className = 'badge-share-btn';
        shareBtn.innerHTML = '&#x1F4E2;'; // Share icon
        shareBtn.title = 'Share this achievement';
        
        shareBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // Prevent triggering the wrapper click
            this.showShareOptions(badge);
        });
        
        wrapper.appendChild(shareBtn);
    }
    
    /**
     * Shows badge sharing options
     * @param {Object} badge - Badge data
     */
    showShareOptions(badge) {
        // Create simple modal for sharing options
        const modal = document.createElement('div');
        modal.className = 'badge-share-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        `;
        
        // Modal content
        const content = document.createElement('div');
        content.style.cssText = `
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 400px;
            width: 90%;
        `;
        
        // Title
        const title = document.createElement('h3');
        title.textContent = 'Share Your Achievement';
        
        // Badge preview
        const preview = document.createElement('div');
        preview.style.cssText = `
            text-align: center;
            margin: 15px 0;
        `;
        
        const img = document.createElement('img');
        img.width = 120;
        img.height = 120;
        img.alt = badge.title || 'Achievement Badge';
        
        // Generate badge SVG for preview
        if (badge.svgContent) {
            img.src = this.generator.createDataUrl(badge.svgContent);
        } else if (badge.imageUrl) {
            img.src = badge.imageUrl;
        } else {
            // Placeholder until generated
            img.src = '/assets/img/badges/loading.svg';
            
            // Generate badge SVG on the fly
            this.generator.generateBadge(
                badge.achievementType, 
                badge.tierLevel,
                badge.userData
            ).then(svgString => {
                img.src = this.generator.createDataUrl(svgString);
            }).catch(error => {
                console.error('Error generating badge for sharing:', error);
                img.src = '/assets/img/badges/fallback.svg';
            });
        }
        
        preview.appendChild(img);
        
        // Share buttons
        const buttons = document.createElement('div');
        buttons.style.cssText = `
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
        `;
        
        // Social media share buttons
        const platforms = [
            { name: 'Facebook', icon: '&#xf09a;', color: '#3b5998' },
            { name: 'Twitter', icon: '&#xf099;', color: '#1da1f2' },
            { name: 'LinkedIn', icon: '&#xf0e1;', color: '#0077b5' }
        ];
        
        platforms.forEach(platform => {
            const btn = document.createElement('button');
            btn.style.cssText = `
                background-color: ${platform.color};
                color: white;
                border: none;
                border-radius: 4px;
                padding: 8px 15px;
                cursor: pointer;
                font-size: 14px;
            `;
            btn.innerHTML = `${platform.icon} ${platform.name}`;
            
            btn.addEventListener('click', () => {
                this.shareToPlatform(platform.name.toLowerCase(), badge);
                document.body.removeChild(modal);
            });
            
            buttons.appendChild(btn);
        });
        
        // Close button
        const closeBtn = document.createElement('button');
        closeBtn.textContent = 'Close';
        closeBtn.style.cssText = `
            background: none;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 8px 15px;
            margin-top: 15px;
            cursor: pointer;
            width: 100%;
        `;
        
        closeBtn.addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        // Assemble modal
        content.appendChild(title);
        content.appendChild(preview);
        content.appendChild(buttons);
        content.appendChild(closeBtn);
        modal.appendChild(content);
        
        // Add to page
        document.body.appendChild(modal);
    }
    
    /**
     * Shares a badge to a specific platform
     * @param {string} platform - Platform name (facebook, twitter, linkedin)
     * @param {Object} badge - Badge data
     */
    shareToPlatform(platform, badge) {
        const title = encodeURIComponent(badge.title || 'I earned an achievement!');
        const url = encodeURIComponent(badge.shareUrl || window.location.href);
        
        let shareUrl;
        
        switch (platform) {
            case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                break;
                
            case 'twitter':
                shareUrl = `https://twitter.com/intent/tweet?text=${title}&url=${url}`;
                break;
                
            case 'linkedin':
                shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
                break;
                
            default:
                console.error('Unsupported sharing platform:', platform);
                return;
        }
        
        // Open share dialog
        window.open(shareUrl, '_blank', 'width=600,height=400');
        
        // Record share event if needed
        if (typeof badge.id !== 'undefined') {
            this.recordBadgeShare(badge.id, platform);
        }
    }
    
    /**
     * Records a badge share event
     * @param {number|string} badgeId - Badge ID
     * @param {string} platform - Platform shared to
     */
    recordBadgeShare(badgeId, platform) {
        // Make API call to record the share
        fetch('/api/badges/share', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                badge_id: badgeId,
                platform: platform,
                timestamp: new Date().toISOString()
            })
        }).catch(error => {
            console.error('Error recording badge share:', error);
        });
    }
    
    /**
     * Animates the entry of a badge element
     * @param {Element} element - Badge element to animate
     */
    animateBadgeEntry(element) {
        // Simple fade-in scale animation
        element.style.opacity = '0';
        element.style.transform = 'scale(0.8)';
        element.style.transition = 'opacity 0.5s, transform 0.5s';
        
        // Trigger animation
        setTimeout(() => {
            element.style.opacity = '1';
            element.style.transform = 'scale(1)';
        }, 50);
    }
    
    /**
     * Displays a newly earned badge with a more prominent animation
     * @param {Object} badge - Badge data
     */
    displayNewBadgeAchievement(badge) {
        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.className = 'badge-achievement-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            color: white;
            text-align: center;
        `;
        
        // Congratulations text
        const congrats = document.createElement('h2');
        congrats.textContent = 'Achievement Unlocked!';
        congrats.style.cssText = `
            font-size: 32px;
            margin-bottom: 20px;
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.5s, transform 0.5s;
        `;
        
        // Badge container
        const badgeContainer = document.createElement('div');
        badgeContainer.style.cssText = `
            margin: 20px 0;
            opacity: 0;
            transform: scale(0.5);
            transition: opacity 0.8s, transform 0.8s;
        `;
        
        // Badge image
        const img = document.createElement('img');
        img.width = 200;
        img.height = 200;
        img.alt = badge.title || 'Achievement Badge';
        
        // Generate badge SVG
        if (badge.svgContent) {
            img.src = this.generator.createDataUrl(badge.svgContent);
        } else if (badge.imageUrl) {
            img.src = badge.imageUrl;
        } else {
            this.generator.generateBadge(
                badge.achievementType, 
                badge.tierLevel,
                badge.userData
            ).then(svgString => {
                img.src = this.generator.createDataUrl(svgString);
            }).catch(error => {
                console.error('Error generating achievement badge:', error);
                img.src = '/assets/img/badges/fallback.svg';
            });
        }
        
        badgeContainer.appendChild(img);
        
        // Badge title
        const title = document.createElement('h3');
        title.textContent = badge.title || 'New Achievement';
        title.style.cssText = `
            font-size: 24px;
            margin-top: 10px;
            opacity: 0;
            transition: opacity 0.5s 0.3s;
        `;
        
        // Description
        const description = document.createElement('p');
        description.textContent = badge.description || '';
        description.style.cssText = `
            font-size: 18px;
            max-width: 80%;
            margin: 10px auto 30px;
            opacity: 0;
            transition: opacity 0.5s 0.5s;
        `;
        
        // Close button
        const closeBtn = document.createElement('button');
        closeBtn.textContent = 'Continue';
        closeBtn.style.cssText = `
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s 0.7s, transform 0.5s 0.7s;
        `;
        
        closeBtn.addEventListener('click', () => {
            // Fade out overlay
            overlay.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(overlay);
            }, 500);
        });
        
        // Assemble overlay
        overlay.appendChild(congrats);
        overlay.appendChild(badgeContainer);
        overlay.appendChild(title);
        overlay.appendChild(description);
        overlay.appendChild(closeBtn);
        
        // Add to page
        document.body.appendChild(overlay);
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.5s';
        
        // Trigger animations
        setTimeout(() => {
            overlay.style.opacity = '1';
            
            setTimeout(() => {
                congrats.style.opacity = '1';
                congrats.style.transform = 'translateY(0)';
            }, 200);
            
            setTimeout(() => {
                badgeContainer.style.opacity = '1';
                badgeContainer.style.transform = 'scale(1)';
            }, 500);
            
            setTimeout(() => {
                title.style.opacity = '1';
            }, 1000);
            
            setTimeout(() => {
                description.style.opacity = '1';
            }, 1200);
            
            setTimeout(() => {
                closeBtn.style.opacity = '1';
                closeBtn.style.transform = 'translateY(0)';
            }, 1400);
        }, 100);
    }
}

// Export the BadgeDisplay class
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BadgeDisplay;
} else {
    window.BadgeDisplay = BadgeDisplay;
}