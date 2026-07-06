#!/bin/bash

# Phase 1 Modules Test Script
# Tests Recipe Management, Menu Engineering, Food Waste, Staff Scheduling, Tip Management, Daily Reports

BASE_URL="http://localhost:8000/api/v1"
TOKEN=""
TEST_RESULTS=()

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo "=========================================="
echo "Phase 1 Modules Test"
echo "=========================================="
echo ""

# Login function
login() {
    echo "Logging in..."
    response=$(curl -s -X POST "$BASE_URL/auth/login" \
        -H "Content-Type: application/json" \
        -d '{"username":"admin","password":"admin123"}')
    
    if echo "$response" | grep -q '"success":true'; then
        TOKEN=$(echo "$response" | grep -o '"access_token":"[^"]*' | cut -d'"' -f4)
        echo -e "${GREEN}✓ Login successful${NC}"
        echo "Token: ${TOKEN:0:50}..."
        echo ""
    else
        echo -e "${RED}✗ Login failed${NC}"
        exit 1
    fi
}

# Test function
test_endpoint() {
    local name=$1
    local method=$2
    local endpoint=$3
    local data=$4
    
    echo "Testing: $name"
    echo "Endpoint: $method $endpoint"
    
    if [ -z "$data" ]; then
        response=$(curl -s -X "$method" "$BASE_URL$endpoint" \
            -H "Authorization: Bearer $TOKEN")
    else
        response=$(curl -s -X "$method" "$BASE_URL$endpoint" \
            -H "Authorization: Bearer $TOKEN" \
            -H "Content-Type: application/json" \
            -d "$data")
    fi
    
    if echo "$response" | grep -q '"success":true'; then
        echo -e "${GREEN}✓ PASS${NC}"
        TEST_RESULTS+=("$name:PASS")
    else
        echo -e "${RED}✗ FAIL${NC}"
        echo "Response: $response"
        TEST_RESULTS+=("$name:FAIL")
    fi
    echo ""
}

# Login
login

# Recipe Management Tests
echo "=========================================="
echo "Recipe Management Tests"
echo "=========================================="
echo ""

test_endpoint "Get Recipes" "GET" "/recipes"

test_endpoint "Create Recipe" "POST" "/recipes" '{
    "product_id": 1,
    "recipe_code": "TEST_'"$(date +%s)"'",
    "recipe_name": "Test Recipe",
    "instructions": "Test instructions",
    "yield_quantity": 1,
    "yield_unit": "portion",
    "preparation_time": 30,
    "production_cost_labor": 10.00,
    "production_cost_equipment": 5.00,
    "production_cost_overhead": 3.00,
    "sourcing_type": "supplier_sourced",
    "difficulty_level": "EASY",
    "status": "ACTIVE",
    "ingredients": []
}'

# Menu Engineering Tests
echo "=========================================="
echo "Menu Engineering Tests"
echo "=========================================="
echo ""

test_endpoint "Menu Mix Analysis" "GET" "/menu-engineering/menu-mix"
test_endpoint "Category Performance" "GET" "/menu-engineering/category-performance"
test_endpoint "Menu Recommendations" "GET" "/menu-engineering/recommendations"
test_endpoint "Food Cost Variance" "GET" "/menu-engineering/food-cost-variance"

# Food Waste Tests
echo "=========================================="
echo "Food Waste Tests"
echo "=========================================="
echo ""

test_endpoint "Get Food Waste Records" "GET" "/food-waste"
test_endpoint "Food Waste Analysis" "GET" "/food-waste/analysis"

test_endpoint "Create Food Waste Record" "POST" "/food-waste" '{
    "waste_date": "2026-07-07",
    "waste_type": "spoilage",
    "quantity": 1.5,
    "unit": "kg",
    "reason": "Test waste record",
    "cost_per_unit": 10.00,
    "total_cost": 15.00
}'

# Staff Scheduling Tests
echo "=========================================="
echo "Staff Scheduling Tests"
echo "=========================================="
echo ""

test_endpoint "Get Shifts" "GET" "/staff-scheduling/shifts"

test_endpoint "Create Shift" "POST" "/staff-scheduling/shifts" '{
    "shift_code": "SHIFT_'"$(date +%s)"'",
    "shift_name": "Morning Shift",
    "start_time": "08:00:00",
    "end_time": "16:00:00",
    "break_duration": 30
}'

test_endpoint "Get Schedules" "GET" "/staff-scheduling/schedules"

# Tip Management Tests
echo "=========================================="
echo "Tip Management Tests"
echo "=========================================="
echo ""

test_endpoint "Get Tips" "GET" "/tips"
test_endpoint "Tip Summary" "GET" "/tips/summary"

test_endpoint "Create Tip Record" "POST" "/tips" '{
    "user_id": 2,
    "tip_date": "2026-07-07",
    "tip_amount": 50.00,
    "tip_type": "cash",
    "payment_method": "cash"
}'

# Daily Reports Tests
echo "=========================================="
echo "Daily Reports Tests"
echo "=========================================="
echo ""

test_endpoint "Daily Sales Report" "GET" "/daily-reports/sales"
test_endpoint "Table Turnover Report" "GET" "/daily-reports/table-turnover"
test_endpoint "Server Performance Report" "GET" "/daily-reports/server-performance"
test_endpoint "Peak Hours Analysis" "GET" "/daily-reports/peak-hours"
test_endpoint "Comprehensive Daily Report" "GET" "/daily-reports/comprehensive"

# Summary
echo "=========================================="
echo "Test Summary"
echo "=========================================="
echo ""

TOTAL_TESTS=${#TEST_RESULTS[@]}
PASSED_TESTS=0
FAILED_TESTS=0

for result in "${TEST_RESULTS[@]}"; do
    if [[ $result == *":PASS" ]]; then
        PASSED_TESTS=$((PASSED_TESTS + 1))
        echo -e "${GREEN}$result${NC}"
    else
        FAILED_TESTS=$((FAILED_TESTS + 1))
        echo -e "${RED}$result${NC}"
    fi
done

echo ""
echo "Total Tests: $TOTAL_TESTS"
echo -e "${GREEN}Passed: $PASSED_TESTS${NC}"
echo -e "${RED}Failed: $FAILED_TESTS${NC}"

if [ $FAILED_TESTS -eq 0 ]; then
    echo -e "${GREEN}All tests passed!${NC}"
    exit 0
else
    echo -e "${RED}Some tests failed${NC}"
    exit 1
fi
