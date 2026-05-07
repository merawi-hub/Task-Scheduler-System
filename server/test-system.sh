#!/bin/bash

# Distributed Task Scheduler - System Test Script
# This script tests the complete system functionality

echo "🧪 Distributed Task Scheduler - System Test"
echo "==========================================="
echo ""

BASE_URL="http://localhost:8000/api"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test counter
TESTS_PASSED=0
TESTS_FAILED=0

# Function to test endpoint
test_endpoint() {
    local name=$1
    local method=$2
    local endpoint=$3
    local data=$4
    local expected_code=$5
    
    echo -n "Testing: $name... "
    
    if [ "$method" = "GET" ]; then
        response=$(curl -s -w "\n%{http_code}" "$BASE_URL$endpoint")
    elif [ "$method" = "POST" ]; then
        response=$(curl -s -w "\n%{http_code}" -X POST "$BASE_URL$endpoint" \
            -H "Content-Type: application/json" \
            -d "$data")
    elif [ "$method" = "DELETE" ]; then
        response=$(curl -s -w "\n%{http_code}" -X DELETE "$BASE_URL$endpoint")
    fi
    
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    if [ "$http_code" = "$expected_code" ]; then
        echo -e "${GREEN}✓ PASSED${NC} (HTTP $http_code)"
        TESTS_PASSED=$((TESTS_PASSED + 1))
        return 0
    else
        echo -e "${RED}✗ FAILED${NC} (Expected $expected_code, got $http_code)"
        echo "Response: $body"
        TESTS_FAILED=$((TESTS_FAILED + 1))
        return 1
    fi
}

echo "1️⃣  Testing API Connectivity"
echo "----------------------------"
test_endpoint "API Test Endpoint" "GET" "/test" "" "200"
echo ""

echo "2️⃣  Testing Worker Management"
echo "----------------------------"
test_endpoint "Register Worker" "POST" "/workers/register" \
    '{"worker_key":"test-worker-001","hostname":"test-host","ip_address":"127.0.0.1"}' "201"

test_endpoint "List Workers" "GET" "/workers" "" "200"

test_endpoint "Worker Heartbeat" "POST" "/workers/test-worker-001/heartbeat" \
    '{"status":"idle"}' "200"

test_endpoint "Get Worker Details" "GET" "/workers/test-worker-001" "" "200"
echo ""

echo "3️⃣  Testing Job Management"
echo "----------------------------"
test_endpoint "Create Job" "POST" "/jobs" \
    '{"name":"Test Job","type":"csv_aggregate","task_count":10,"priority":5}' "201"

test_endpoint "List Jobs" "GET" "/jobs" "" "200"

test_endpoint "Get Job Details" "GET" "/jobs/1" "" "200"

test_endpoint "Get Job Tasks" "GET" "/jobs/1/tasks" "" "200"
echo ""

echo "4️⃣  Testing Task Management"
echo "----------------------------"
test_endpoint "Claim Next Task" "GET" "/tasks/next" "" "200"

# Note: The following tests require a claimed task
# test_endpoint "Start Task" "POST" "/tasks/1/start" \
#     '{"worker_key":"test-worker-001"}' "200"

# test_endpoint "Complete Task" "POST" "/tasks/1/complete" \
#     '{"worker_key":"test-worker-001","result":{"processed":100},"duration_ms":1000}' "200"
echo ""

echo "5️⃣  Testing Metrics"
echo "----------------------------"
test_endpoint "Get System Metrics" "GET" "/metrics" "" "200"

test_endpoint "Get Metrics History" "GET" "/metrics/history" "" "200"
echo ""

echo "6️⃣  Testing Job Cancellation"
echo "----------------------------"
# Create another job for cancellation test
curl -s -X POST "$BASE_URL/jobs" \
    -H "Content-Type: application/json" \
    -d '{"name":"Job to Cancel","type":"test","task_count":5,"priority":1}' > /dev/null

test_endpoint "Cancel Job" "DELETE" "/jobs/2" "" "200"
echo ""

echo "==========================================="
echo "📊 Test Results"
echo "==========================================="
echo -e "Tests Passed: ${GREEN}$TESTS_PASSED${NC}"
echo -e "Tests Failed: ${RED}$TESTS_FAILED${NC}"
echo "Total Tests: $((TESTS_PASSED + TESTS_FAILED))"
echo ""

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}✅ All tests passed!${NC}"
    exit 0
else
    echo -e "${RED}❌ Some tests failed!${NC}"
    exit 1
fi
