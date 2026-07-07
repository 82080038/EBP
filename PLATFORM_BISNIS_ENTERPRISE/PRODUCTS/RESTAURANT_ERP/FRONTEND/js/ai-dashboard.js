/**
 * AI Dashboard Module
 * Handles AI & Analytics functionality including:
 * - Demand Forecasting
 * - Menu Optimization
 * - Customer Intelligence
 * - Kitchen Intelligence
 * - Waste Reduction
 * - Smart Procurement
 */

class AIDashboard {
    constructor() {
        this.currentTab = 'forecasting';
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadInitialData();
    }

    bindEvents() {
        // Tab switching
        document.querySelectorAll('.ai-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                this.switchTab(e.target.dataset.tab);
            });
        });

        // Demand Forecasting
        document.getElementById('generateForecastBtn')?.addEventListener('click', () => {
            this.generateSalesForecast();
        });

        // Menu Optimization
        document.getElementById('analyzeMenuBtn')?.addEventListener('click', () => {
            this.analyzeMenu();
        });

        // Customer Intelligence
        document.getElementById('segmentCustomersBtn')?.addEventListener('click', () => {
            this.segmentCustomers();
        });

        // Kitchen Intelligence
        document.getElementById('analyzeKitchenBtn')?.addEventListener('click', () => {
            this.analyzeKitchen();
        });

        // Waste Reduction
        document.getElementById('predictWasteBtn')?.addEventListener('click', () => {
            this.predictWaste();
        });

        // Smart Procurement
        document.getElementById('getProcurementBtn')?.addEventListener('click', () => {
            this.getProcurementRecommendations();
        });
    }

    switchTab(tabName) {
        // Update tab buttons
        document.querySelectorAll('.ai-tab').forEach(tab => {
            tab.classList.remove('active');
            if (tab.dataset.tab === tabName) {
                tab.classList.add('active');
            }
        });

        // Update tab content
        document.querySelectorAll('.ai-tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`${tabName}Tab`)?.classList.add('active');

        this.currentTab = tabName;
    }

    async loadInitialData() {
        // Load initial forecast data
        await this.getSalesForecast();
    }

    // Demand Forecasting Methods
    async generateSalesForecast() {
        const days = document.getElementById('forecastDays')?.value || 7;
        const chartContainer = document.getElementById('salesForecastChart');
        
        try {
            chartContainer.innerHTML = '<p class="loading">Generating forecast...</p>';
            
            const result = await window.apiClient.generateSalesForecast(parseInt(days));
            
            if (result.success) {
                this.renderSalesForecastChart(result.data);
            } else {
                chartContainer.innerHTML = `<p class="error">Failed to generate forecast: ${result.message}</p>`;
            }
        } catch (error) {
            chartContainer.innerHTML = `<p class="error">Error: ${error.message}</p>`;
        }
    }

    async getSalesForecast() {
        try {
            const result = await window.apiClient.getSalesForecast();
            if (result.success && result.data) {
                this.renderSalesForecastChart(result.data);
            }
        } catch (error) {
            console.error('Failed to load forecast:', error);
        }
    }

    renderSalesForecastChart(data) {
        const chartContainer = document.getElementById('salesForecastChart');
        
        if (!data || data.length === 0) {
            chartContainer.innerHTML = '<p class="placeholder">No forecast data available</p>';
            return;
        }

        let html = '<div class="forecast-chart-container">';
        html += '<table class="forecast-table">';
        html += '<thead><tr><th>Date</th><th>Predicted Revenue</th><th>Confidence</th></tr></thead>';
        html += '<tbody>';
        
        data.forEach(item => {
            const revenue = this.formatCurrency(item.predicted_revenue);
            const confidence = Math.round(item.confidence_score * 100) + '%';
            html += `<tr><td>${item.date}</td><td>${revenue}</td><td>${confidence}</td></tr>`;
        });
        
        html += '</tbody></table>';
        html += '</div>';
        
        chartContainer.innerHTML = html;
    }

    async getInventoryPredictions() {
        try {
            const result = await window.apiClient.getInventoryPredictions();
            if (result.success) {
                this.renderInventoryPredictions(result.data);
            }
        } catch (error) {
            console.error('Failed to load inventory predictions:', error);
        }
    }

    renderInventoryPredictions(data) {
        const container = document.getElementById('inventoryPredictions');
        
        if (!data || data.length === 0) {
            container.innerHTML = '<p class="placeholder">No inventory predictions available</p>';
            return;
        }

        let html = '<div class="inventory-predictions-grid">';
        data.forEach(item => {
            const daysRemaining = Math.floor(item.days_until_empty);
            const urgencyClass = daysRemaining < 3 ? 'urgent' : daysRemaining < 7 ? 'warning' : 'normal';
            
            html += `
                <div class="prediction-card ${urgencyClass}">
                    <h5>Item #${item.inventory_id}</h5>
                    <p>Current Stock: ${item.current_stock}</p>
                    <p>Daily Usage: ${item.daily_usage.toFixed(2)}</p>
                    <p>Days Until Empty: ${daysRemaining}</p>
                    <p>Reorder Date: ${item.reorder_date || 'N/A'}</p>
                    <p>Recommended Qty: ${item.recommended_quantity}</p>
                </div>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
    }

    // Menu Optimization Methods
    async analyzeMenu() {
        const container = document.getElementById('menuAnalysis');
        
        try {
            container.innerHTML = '<p class="loading">Analyzing menu...</p>';
            
            const result = await window.apiClient.getMenuOptimization();
            
            if (result.success) {
                this.renderMenuAnalysis(result.data);
            } else {
                container.innerHTML = `<p class="error">Failed to analyze menu: ${result.message}</p>`;
            }
        } catch (error) {
            container.innerHTML = `<p class="error">Error: ${error.message}</p>`;
        }
    }

    renderMenuAnalysis(data) {
        const container = document.getElementById('menuAnalysis');
        
        if (!data || data.length === 0) {
            container.innerHTML = '<p class="placeholder">No menu analysis data available</p>';
            return;
        }

        let html = '<div class="menu-analysis-grid">';
        data.forEach(item => {
            const performanceClass = item.performance_score >= 80 ? 'high' : item.performance_score >= 60 ? 'medium' : 'low';
            
            html += `
                <div class="menu-item-card ${performanceClass}">
                    <h5>${item.product_name}</h5>
                    <p>Performance Score: ${item.performance_score}%</p>
                    <p>Sales Count: ${item.sales_count}</p>
                    <p>Revenue: ${this.formatCurrency(item.revenue)}</p>
                    <p>Recommendation: ${item.recommendation}</p>
                </div>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
    }

    async getDynamicPricing(productId) {
        try {
            const result = await window.apiClient.getDynamicPricing(productId);
            if (result.success) {
                return result.data;
            }
        } catch (error) {
            console.error('Failed to get dynamic pricing:', error);
        }
        return null;
    }

    // Customer Intelligence Methods
    async segmentCustomers() {
        const container = document.getElementById('customerSegments');
        
        try {
            container.innerHTML = '<p class="loading">Segmenting customers...</p>';
            
            const result = await window.apiClient.getCustomerSegmentation();
            
            if (result.success) {
                this.renderCustomerSegments(result.data);
            } else {
                container.innerHTML = `<p class="error">Failed to segment customers: ${result.message}</p>`;
            }
        } catch (error) {
            container.innerHTML = `<p class="error">Error: ${error.message}</p>`;
        }
    }

    renderCustomerSegments(data) {
        const container = document.getElementById('customerSegments');
        
        if (!data || data.length === 0) {
            container.innerHTML = '<p class="placeholder">No customer segments available</p>';
            return;
        }

        let html = '<div class="customer-segments-grid">';
        data.forEach(segment => {
            html += `
                <div class="segment-card">
                    <h5>${segment.segment_name}</h5>
                    <p>Customer Count: ${segment.customer_count}</p>
                    <p>Average Order Value: ${this.formatCurrency(segment.avg_order_value)}</p>
                    <p>Visit Frequency: ${segment.visit_frequency} visits/month</p>
                    <p>Characteristics: ${segment.characteristics}</p>
                </div>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
    }

    async getChurnPrediction() {
        try {
            const result = await window.apiClient.getChurnPrediction();
            if (result.success) {
                this.renderChurnPrediction(result.data);
            }
        } catch (error) {
            console.error('Failed to get churn prediction:', error);
        }
    }

    renderChurnPrediction(data) {
        const container = document.getElementById('churnPrediction');
        
        if (!data || data.length === 0) {
            container.innerHTML = '<p class="placeholder">No churn prediction data available</p>';
            return;
        }

        let html = '<div class="churn-prediction-grid">';
        data.forEach(item => {
            const riskClass = item.churn_risk >= 70 ? 'high' : item.churn_risk >= 40 ? 'medium' : 'low';
            
            html += `
                <div class="churn-card ${riskClass}">
                    <h5>Customer #${item.customer_id}</h5>
                    <p>Churn Risk: ${item.churn_risk}%</p>
                    <p>Last Visit: ${item.last_visit}</p>
                    <p>Total Orders: ${item.total_orders}</p>
                    <p>Recommendation: ${item.recommendation}</p>
                </div>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
    }

    // Kitchen Intelligence Methods
    async analyzeKitchen() {
        const container = document.getElementById('kitchenEfficiency');
        
        try {
            container.innerHTML = '<p class="loading">Analyzing kitchen...</p>';
            
            const result = await window.apiClient.getKitchenEfficiency();
            
            if (result.success) {
                this.renderKitchenEfficiency(result.data);
            } else {
                container.innerHTML = `<p class="error">Failed to analyze kitchen: ${result.message}</p>`;
            }
        } catch (error) {
            container.innerHTML = `<p class="error">Error: ${error.message}</p>`;
        }
    }

    renderKitchenEfficiency(data) {
        const container = document.getElementById('kitchenEfficiency');
        
        if (!data) {
            container.innerHTML = '<p class="placeholder">No kitchen efficiency data available</p>';
            return;
        }

        let html = `
            <div class="kitchen-metrics">
                <div class="metric-card">
                    <h5>Average Prep Time</h5>
                    <p>${data.avg_prep_time} minutes</p>
                </div>
                <div class="metric-card">
                    <h5>Orders/Hour</h5>
                    <p>${data.orders_per_hour}</p>
                </div>
                <div class="metric-card">
                    <h5>Efficiency Score</h5>
                    <p>${data.efficiency_score}%</p>
                </div>
                <div class="metric-card">
                    <h5>Bottlenecks</h5>
                    <p>${data.bottlenecks || 'None'}</p>
                </div>
            </div>
        `;
        
        container.innerHTML = html;
    }

    async getPreparationTimePrediction(orderId) {
        try {
            const result = await window.apiClient.getPreparationTimePrediction(orderId);
            if (result.success) {
                return result.data;
            }
        } catch (error) {
            console.error('Failed to get prep time prediction:', error);
        }
        return null;
    }

    // Waste Reduction Methods
    async predictWaste() {
        const container = document.getElementById('wastePrediction');
        
        try {
            container.innerHTML = '<p class="loading">Predicting waste...</p>';
            
            const result = await window.apiClient.getWastePrediction();
            
            if (result.success) {
                this.renderWastePrediction(result.data);
            } else {
                container.innerHTML = `<p class="error">Failed to predict waste: ${result.message}</p>`;
            }
        } catch (error) {
            container.innerHTML = `<p class="error">Error: ${error.message}</p>`;
        }
    }

    renderWastePrediction(data) {
        const container = document.getElementById('wastePrediction');
        
        if (!data || data.length === 0) {
            container.innerHTML = '<p class="placeholder">No waste prediction data available</p>';
            return;
        }

        let html = '<div class="waste-prediction-grid">';
        data.forEach(item => {
            const severityClass = item.predicted_waste > 1000 ? 'high' : item.predicted_waste > 500 ? 'medium' : 'low';
            
            html += `
                <div class="waste-card ${severityClass}">
                    <h5>${item.item_name}</h5>
                    <p>Predicted Waste: ${item.predicted_waste} kg</p>
                    <p>Cost Impact: ${this.formatCurrency(item.cost_impact)}</p>
                    <p>Primary Cause: ${item.primary_cause}</p>
                </div>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
    }

    async getWasteReductionRecommendations() {
        try {
            const result = await window.apiClient.getWasteReductionRecommendations();
            if (result.success) {
                this.renderWasteRecommendations(result.data);
            }
        } catch (error) {
            console.error('Failed to get waste recommendations:', error);
        }
    }

    renderWasteRecommendations(data) {
        const container = document.getElementById('wasteRecommendations');
        
        if (!data || data.length === 0) {
            container.innerHTML = '<p class="placeholder">No waste reduction recommendations available</p>';
            return;
        }

        let html = '<ul class="recommendations-list">';
        data.forEach(item => {
            html += `<li><strong>${item.priority}:</strong> ${item.recommendation} (Est. Savings: ${this.formatCurrency(item.estimated_savings)})</li>`;
        });
        html += '</ul>';
        
        container.innerHTML = html;
    }

    // Smart Procurement Methods
    async getProcurementRecommendations() {
        const container = document.getElementById('procurementRecommendations');
        
        try {
            container.innerHTML = '<p class="loading">Getting recommendations...</p>';
            
            const result = await window.apiClient.getProcurementRecommendations();
            
            if (result.success) {
                this.renderProcurementRecommendations(result.data);
            } else {
                container.innerHTML = `<p class="error">Failed to get recommendations: ${result.message}</p>`;
            }
        } catch (error) {
            container.innerHTML = `<p class="error">Error: ${error.message}</p>`;
        }
    }

    renderProcurementRecommendations(data) {
        const container = document.getElementById('procurementRecommendations');
        
        if (!data || data.length === 0) {
            container.innerHTML = '<p class="placeholder">No procurement recommendations available</p>';
            return;
        }

        let html = '<div class="procurement-grid">';
        data.forEach(item => {
            const urgencyClass = item.urgency === 'high' ? 'urgent' : item.urgency === 'medium' ? 'warning' : 'normal';
            
            html += `
                <div class="procurement-card ${urgencyClass}">
                    <h5>${item.item_name}</h5>
                    <p>Recommended Quantity: ${item.recommended_quantity}</p>
                    <p>Estimated Cost: ${this.formatCurrency(item.estimated_cost)}</p>
                    <p>Supplier: ${item.preferred_supplier}</p>
                    <p>Order By: ${item.order_by_date}</p>
                </div>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
    }

    async getSupplierPerformance() {
        try {
            const result = await window.apiClient.getSupplierPerformance();
            if (result.success) {
                this.renderSupplierPerformance(result.data);
            }
        } catch (error) {
            console.error('Failed to get supplier performance:', error);
        }
    }

    renderSupplierPerformance(data) {
        const container = document.getElementById('supplierPerformance');
        
        if (!data || data.length === 0) {
            container.innerHTML = '<p class="placeholder">No supplier performance data available</p>';
            return;
        }

        let html = '<table class="supplier-table">';
        html += '<thead><tr><th>Supplier</th><th>On-Time Delivery</th><th>Quality Score</th><th>Price Competitiveness</th></tr></thead>';
        html += '<tbody>';
        
        data.forEach(item => {
            const performanceClass = item.overall_score >= 80 ? 'high' : item.overall_score >= 60 ? 'medium' : 'low';
            html += `<tr class="${performanceClass}"><td>${item.supplier_name}</td><td>${item.on_time_delivery}%</td><td>${item.quality_score}%</td><td>${item.price_score}%</td></tr>`;
        });
        
        html += '</tbody></table>';
        
        container.innerHTML = html;
    }

    // Advanced Analytics Methods
    async getAdvancedInsights() {
        try {
            const result = await window.apiClient.getAdvancedInsights();
            if (result.success) {
                return result.data;
            }
        } catch (error) {
            console.error('Failed to get advanced insights:', error);
        }
        return null;
    }

    async getPredictiveAnalytics() {
        try {
            const result = await window.apiClient.getPredictiveAnalytics();
            if (result.success) {
                return result.data;
            }
        } catch (error) {
            console.error('Failed to get predictive analytics:', error);
        }
        return null;
    }

    // Utility Methods
    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }
}

// Initialize AI Dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('aiPage')) {
        window.aiDashboard = new AIDashboard();
    }
});
