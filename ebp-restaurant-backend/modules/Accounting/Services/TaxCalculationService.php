<?php

require_once __DIR__ . '/../Repositories/TaxCalculationRepository.php';
require_once __DIR__ . '/../../../config/database.php';

class TaxCalculationService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new TaxCalculationRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function calculateOrderTax($orderId, $tenantId)
    {
        try {
            $order = $this->repository->getOrder($orderId, $tenantId);
            
            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'Order not found'
                ];
            }

            $taxRate = $this->repository->getTaxRate($tenantId, $order['branch_id']);
            $ppnRate = $taxRate['ppn_rate'] ?? 11; // Default 11% PPN in Indonesia
            $pb1Rate = $taxRate['pb1_rate'] ?? 10; // Default 10% PB1 for restaurants

            $taxableAmount = $order['total_amount'] - $order['discount'];
            $ppnTax = $taxableAmount * ($ppnRate / 100);
            $pb1Tax = $taxableAmount * ($pb1Rate / 100);
            $totalTax = $ppnTax + $pb1Tax;

            return [
                'success' => true,
                'message' => 'Tax calculated successfully',
                'data' => [
                    'ppn_rate' => $ppnRate,
                    'pb1_rate' => $pb1Rate,
                    'taxable_amount' => $taxableAmount,
                    'ppn_tax' => $ppnTax,
                    'pb1_tax' => $pb1Tax,
                    'total_tax' => $totalTax,
                    'net_amount' => $taxableAmount + $totalTax
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to calculate tax: ' . $e->getMessage()
            ];
        }
    }

    public function calculateMonthlyTax($tenantId, $branchId, $year, $month)
    {
        try {
            $startDate = "$year-$month-01";
            $endDate = date('Y-m-t', strtotime($startDate));

            $orders = $this->repository->getOrdersForTax($tenantId, $branchId, $startDate, $endDate);
            
            $taxRate = $this->repository->getTaxRate($tenantId, $branchId);
            $ppnRate = $taxRate['ppn_rate'] ?? 11;
            $pb1Rate = $taxRate['pb1_rate'] ?? 10;

            $totalGross = 0;
            $totalDiscount = 0;
            $totalTaxable = 0;
            $totalPPN = 0;
            $totalPB1 = 0;
            $totalNonTaxable = 0;

            foreach ($orders as $order) {
                $totalGross += $order['total_amount'];
                $totalDiscount += $order['discount'];
                
                $isTaxable = $order['is_taxable'] ?? true; // Default to taxable
                if ($isTaxable) {
                    $taxableAmount = $order['total_amount'] - $order['discount'];
                    $totalTaxable += $taxableAmount;
                    $totalPPN += $taxableAmount * ($ppnRate / 100);
                    $totalPB1 += $taxableAmount * ($pb1Rate / 100);
                } else {
                    $totalNonTaxable += $order['total_amount'];
                }
            }

            return [
                'success' => true,
                'message' => 'Monthly tax calculated successfully',
                'data' => [
                    'year' => $year,
                    'month' => $month,
                    'total_gross' => $totalGross,
                    'total_discount' => $totalDiscount,
                    'total_taxable' => $totalTaxable,
                    'total_non_taxable' => $totalNonTaxable,
                    'ppn_rate' => $ppnRate,
                    'ppn_tax' => $totalPPN,
                    'pb1_rate' => $pb1Rate,
                    'pb1_tax' => $totalPB1,
                    'total_tax' => $totalPPN + $totalPB1,
                    'net_sales' => $totalTaxable + $totalNonTaxable - $totalDiscount
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to calculate monthly tax: ' . $e->getMessage()
            ];
        }
    }

    public function saveTaxRate($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['ppn_rate']) || empty($data['pb1_rate'])) {
                return [
                    'success' => false,
                    'message' => 'PPN rate and PB1 rate are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            
            $existing = $this->repository->getTaxRate($tenantId, $branchId);
            
            if ($existing) {
                $this->repository->updateTaxRate($existing['tax_rate_id'], $data);
                $taxRateId = $existing['tax_rate_id'];
            } else {
                $taxRateId = $this->repository->createTaxRate($data);
            }

            return [
                'success' => true,
                'message' => 'Tax rate saved successfully',
                'tax_rate_id' => $taxRateId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to save tax rate: ' . $e->getMessage()
            ];
        }
    }

    public function getTaxRate($tenantId, $branchId)
    {
        try {
            $taxRate = $this->repository->getTaxRate($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Tax rate retrieved successfully',
                'data' => $taxRate
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get tax rate: ' . $e->getMessage()
            ];
        }
    }

    public function generateTaxReport($tenantId, $branchId, $year, $month)
    {
        try {
            $taxData = $this->calculateMonthlyTax($tenantId, $branchId, $year, $month);
            
            if (!$taxData['success']) {
                return $taxData;
            }

            $data = $taxData['data'];
            
            // Generate PPN report format (SPT Masa PPN)
            $ppnReport = [
                'report_type' => 'SPT_MASA_PPN',
                'masa_pajak' => $month,
                'tahun_pajak' => $year,
                'omzet' => $data['total_taxable'],
                'ppn_terutang' => $data['ppn_tax'],
                'ppn_dibayar' => 0, // Calculate from payments
                'selisih' => $data['ppn_tax']
            ];

            // Generate PB1 report format
            $pb1Report = [
                'report_type' => 'SPT_MASA_PB1',
                'masa_pajak' => $month,
                'tahun_pajak' => $year,
                'omzet' => $data['total_taxable'],
                'pb1_terutang' => $data['pb1_tax'],
                'pb1_dibayar' => 0,
                'selisih' => $data['pb1_tax']
            ];

            return [
                'success' => true,
                'message' => 'Tax report generated successfully',
                'data' => [
                    'summary' => $data,
                    'ppn_report' => $ppnReport,
                    'pb1_report' => $pb1Report
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to generate tax report: ' . $e->getMessage()
            ];
        }
    }
}
