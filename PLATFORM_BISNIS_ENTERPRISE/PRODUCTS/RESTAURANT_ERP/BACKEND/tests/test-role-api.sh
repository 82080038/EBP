#!/bin/bash

# Role-Based UI API Test Script
# Tests login and role-based access for different user roles

BASE_URL="http://localhost:8000/api/v1"
TEST_RESULTS=""

echo "=========================================="
echo "Role-Based UI API Test"
echo "=========================================="
echo ""

# Test users with their expected roles
declare -A TEST_USERS=(
    ["admin"]="Administrator:TENANT_OWNER"
    ["resto_manager"]="Restaurant Manager:TENANT_MEMBER"
    ["resto_waiter"]="Waiter:TENANT_MEMBER"
    ["resto_kitchen"]="Kitchen Staff:TENANT_MEMBER"
    ["resto_cashier"]="Cashier:TENANT_MEMBER"
)

# Expected menu tabs for each role
declare -A EXPECTED_TABS=(
    ["Administrator"]="overview menu tables orders inventory kitchen users settings accounting reservation crm reports hr delivery ai integration quality supplychain sustainability location maintenance whatsapp loyalty"
    ["Restaurant Manager"]="overview menu tables orders inventory kitchen reservation reports hr crm delivery supplychain quality accounting"
    ["Waiter"]="overview tables orders reservation menu"
    ["Kitchen Staff"]="overview kitchen orders inventory menu"
    ["Cashier"]="overview orders accounting reports tables menu"
)

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test login function
test_login() {
    local username=$1
    local password=$2
    local expected_role=$3
    
    echo "Testing login for: $username"
    echo "Expected role: $expected_role"
    
    response=$(curl -s -X POST "$BASE_URL/auth/login" \
        -H "Content-Type: application/json" \
        -d "{\"username\":\"$username\",\"password\":\"$password\"}")
    
    # Check if login successful
    if echo "$response" | grep -q '"success":true'; then
        echo -e "${GREEN}✓ Login successful${NC}"
        
        # Extract token
        token=$(echo "$response" | grep -o '"access_token":"[^"]*' | cut -d'"' -f4)
        
        # Extract user role
        user_role=$(echo "$response" | grep -o '"role":"[^"]*' | cut -d'"' -f4)
        
        if [ "$user_role" == "$expected_role" ]; then
            echo -e "${GREEN}✓ Role matches: $user_role${NC}"
        else
            echo -e "${RED}✗ Role mismatch. Expected: $expected_role, Got: $user_role${NC}"
        fi
        
        echo "Token: ${token:0:50}..."
        echo ""
        
        # Return token for further tests
        echo "$token"
    else
        echo -e "${RED}✗ Login failed${NC}"
        echo "Response: $response"
        echo ""
        echo "FAILED"
    fi
}

# Test menu access
test_menu_access() {
    local username=$1
    local token=$2
    
    echo "Testing menu access for: $username"
    
    # Get user info
    response=$(curl -s -X GET "$BASE_URL/users/me" \
        -H "Authorization: Bearer $token")
    
    if echo "$response" | grep -q '"success":true'; then
        echo -e "${GREEN}✓ User info retrieved${NC}"
        echo "Response: $response" | head -20
    else
        echo -e "${RED}✗ Failed to get user info${NC}"
    fi
    echo ""
}

# Run tests for each user
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

for username in "${!TEST_USERS[@]}"; do
    IFS=':' read -r expected_role expected_level <<< "${TEST_USERS[$username]}"
    
    echo "=========================================="
    echo "Testing: $username"
    echo "=========================================="
    
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    
    # Test login
    if [ "$username" == "admin" ]; then
        password="admin123"
    else
        password="password"
    fi
    
    result=$(test_login "$username" "$password" "$expected_role")
    
    if [ "$result" != "FAILED" ]; then
        PASSED_TESTS=$((PASSED_TESTS + 1))
        
        # Test menu access
        test_menu_access "$username" "$result"
    else
        FAILED_TESTS=$((FAILED_TESTS + 1))
    fi
    
    echo ""
    sleep 1
done

# Summary
echo "=========================================="
echo "Test Summary"
echo "=========================================="
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
