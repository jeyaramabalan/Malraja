<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddPerformanceIndexes extends Migration
{
    private function hasIndex(string $table, string $indexName): bool
    {
        // Works on MySQL: check existing index by Key_name.
        $rows = DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$indexName]);
        return !empty($rows);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('order')) {
            Schema::table('order', function (Blueprint $table) {
                if (!$this->hasIndex('order', 'idx_order_bill_id')) $table->index('bill_id', 'idx_order_bill_id');
                if (!$this->hasIndex('order', 'idx_order_customer_id')) $table->index('customer_id', 'idx_order_customer_id');
                if (!$this->hasIndex('order', 'idx_order_created_by')) $table->index('created_by', 'idx_order_created_by');
                if (!$this->hasIndex('order', 'idx_order_status')) $table->index('status', 'idx_order_status');
                if (!$this->hasIndex('order', 'idx_order_date')) $table->index('date', 'idx_order_date');
                if (!$this->hasIndex('order', 'idx_order_status_date')) $table->index(['status', 'date'], 'idx_order_status_date');
                if (!$this->hasIndex('order', 'idx_order_return_status')) $table->index('return_status', 'idx_order_return_status');
            });
        }

        if (Schema::hasTable('collection')) {
            Schema::table('collection', function (Blueprint $table) {
                if (!$this->hasIndex('collection', 'idx_collection_order_id')) $table->index('order_id', 'idx_collection_order_id');
                if (!$this->hasIndex('collection', 'idx_collection_status')) $table->index('status', 'idx_collection_status');
                if (!$this->hasIndex('collection', 'idx_collection_date')) $table->index('date', 'idx_collection_date');
                if (!$this->hasIndex('collection', 'idx_collection_collected_by')) $table->index('collected_by', 'idx_collection_collected_by');
                if (!$this->hasIndex('collection', 'idx_collection_created_by')) $table->index('created_by', 'idx_collection_created_by');
                if (!$this->hasIndex('collection', 'idx_collection_order_status')) $table->index(['order_id', 'status'], 'idx_collection_order_status');
            });
        }

        if (Schema::hasTable('stock')) {
            Schema::table('stock', function (Blueprint $table) {
                if (!$this->hasIndex('stock', 'idx_stock_product_id')) $table->index('product_id', 'idx_stock_product_id');
                if (!$this->hasIndex('stock', 'idx_stock_category_id')) $table->index('category_id', 'idx_stock_category_id');
                if (!$this->hasIndex('stock', 'idx_stock_hsn_id')) $table->index('hsn_id', 'idx_stock_hsn_id');
                if (!$this->hasIndex('stock', 'idx_stock_date')) $table->index('date', 'idx_stock_date');
                if (!$this->hasIndex('stock', 'idx_stock_bill')) $table->index('bill', 'idx_stock_bill');
                if (!$this->hasIndex('stock', 'idx_stock_product_date')) $table->index(['product_id', 'date'], 'idx_stock_product_date');
            });
        }

        if (Schema::hasTable('visits')) {
            Schema::table('visits', function (Blueprint $table) {
                if (!$this->hasIndex('visits', 'idx_visits_customer')) $table->index('customer', 'idx_visits_customer');
                if (!$this->hasIndex('visits', 'idx_visits_user_id')) $table->index('user_id', 'idx_visits_user_id');
                if (!$this->hasIndex('visits', 'idx_visits_purpose_id')) $table->index('purpose_of_visit_id', 'idx_visits_purpose_id');
                if (!$this->hasIndex('visits', 'idx_visits_follow_up')) $table->index('follow_up_needed', 'idx_visits_follow_up');
                if (!$this->hasIndex('visits', 'idx_visits_created_at')) $table->index('created_at', 'idx_visits_created_at');
                if (!$this->hasIndex('visits', 'idx_visits_user_created_at')) $table->index(['user_id', 'created_at'], 'idx_visits_user_created_at');
            });
        }

        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                if (!$this->hasIndex('order_details', 'idx_order_details_product_id')) $table->index('product_id', 'idx_order_details_product_id');
                if (!$this->hasIndex('order_details', 'idx_order_details_category_id')) $table->index('category_id', 'idx_order_details_category_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('order')) {
            Schema::table('order', function (Blueprint $table) {
                if ($this->hasIndex('order', 'idx_order_bill_id')) $table->dropIndex('idx_order_bill_id');
                if ($this->hasIndex('order', 'idx_order_customer_id')) $table->dropIndex('idx_order_customer_id');
                if ($this->hasIndex('order', 'idx_order_created_by')) $table->dropIndex('idx_order_created_by');
                if ($this->hasIndex('order', 'idx_order_status')) $table->dropIndex('idx_order_status');
                if ($this->hasIndex('order', 'idx_order_date')) $table->dropIndex('idx_order_date');
                if ($this->hasIndex('order', 'idx_order_status_date')) $table->dropIndex('idx_order_status_date');
                if ($this->hasIndex('order', 'idx_order_return_status')) $table->dropIndex('idx_order_return_status');
            });
        }

        if (Schema::hasTable('collection')) {
            Schema::table('collection', function (Blueprint $table) {
                if ($this->hasIndex('collection', 'idx_collection_order_id')) $table->dropIndex('idx_collection_order_id');
                if ($this->hasIndex('collection', 'idx_collection_status')) $table->dropIndex('idx_collection_status');
                if ($this->hasIndex('collection', 'idx_collection_date')) $table->dropIndex('idx_collection_date');
                if ($this->hasIndex('collection', 'idx_collection_collected_by')) $table->dropIndex('idx_collection_collected_by');
                if ($this->hasIndex('collection', 'idx_collection_created_by')) $table->dropIndex('idx_collection_created_by');
                if ($this->hasIndex('collection', 'idx_collection_order_status')) $table->dropIndex('idx_collection_order_status');
            });
        }

        if (Schema::hasTable('stock')) {
            Schema::table('stock', function (Blueprint $table) {
                if ($this->hasIndex('stock', 'idx_stock_product_id')) $table->dropIndex('idx_stock_product_id');
                if ($this->hasIndex('stock', 'idx_stock_category_id')) $table->dropIndex('idx_stock_category_id');
                if ($this->hasIndex('stock', 'idx_stock_hsn_id')) $table->dropIndex('idx_stock_hsn_id');
                if ($this->hasIndex('stock', 'idx_stock_date')) $table->dropIndex('idx_stock_date');
                if ($this->hasIndex('stock', 'idx_stock_bill')) $table->dropIndex('idx_stock_bill');
                if ($this->hasIndex('stock', 'idx_stock_product_date')) $table->dropIndex('idx_stock_product_date');
            });
        }

        if (Schema::hasTable('visits')) {
            Schema::table('visits', function (Blueprint $table) {
                if ($this->hasIndex('visits', 'idx_visits_customer')) $table->dropIndex('idx_visits_customer');
                if ($this->hasIndex('visits', 'idx_visits_user_id')) $table->dropIndex('idx_visits_user_id');
                if ($this->hasIndex('visits', 'idx_visits_purpose_id')) $table->dropIndex('idx_visits_purpose_id');
                if ($this->hasIndex('visits', 'idx_visits_follow_up')) $table->dropIndex('idx_visits_follow_up');
                if ($this->hasIndex('visits', 'idx_visits_created_at')) $table->dropIndex('idx_visits_created_at');
                if ($this->hasIndex('visits', 'idx_visits_user_created_at')) $table->dropIndex('idx_visits_user_created_at');
            });
        }

        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                if ($this->hasIndex('order_details', 'idx_order_details_product_id')) $table->dropIndex('idx_order_details_product_id');
                if ($this->hasIndex('order_details', 'idx_order_details_category_id')) $table->dropIndex('idx_order_details_category_id');
            });
        }
    }
}
