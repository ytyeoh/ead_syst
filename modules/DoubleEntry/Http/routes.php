<?php

Route::group([
    'middleware' => 'admin',
    'prefix' => 'double-entry',
    'namespace' => 'Modules\DoubleEntry\Http\Controllers'
], function () {
    Route::get('chart-of-accounts/{chart_of_account}/enable', 'ChartOfAccounts@enable')->name('chart-of-accounts.enable');
    Route::get('chart-of-accounts/{chart_of_account}/disable', 'ChartOfAccounts@disable')->name('chart-of-accounts.disable');
    Route::get('chart-of-accounts/{chart_of_account}/duplicate', 'ChartOfAccounts@duplicate')->name('chart-of-accounts.duplicate');
    Route::post('chart-of-accounts/import', 'ChartOfAccounts@import')->name('chart-of-accounts.import');
    Route::get('chart-of-accounts/export', 'ChartOfAccounts@export')->name('chart-of-accounts.export');
    Route::resource('chart-of-accounts', 'ChartOfAccounts');

    Route::get('journal-entry/addItem', 'JournalEntry@addItem')->middleware(['double-entry-money'])->name('journal-entry.add.item');
    Route::post('journal-entry/totalItem', 'JournalEntry@totalItem')->middleware(['double-entry-money'])->name('journal-entry.total.item');
    Route::resource('journal-entry', 'JournalEntry', ['middleware' => ['double-entry-money']]);

    Route::get('general-ledger/export', 'GeneralLedger@export')->name('general-ledger.export');
    Route::resource('general-ledger', 'GeneralLedger');
    Route::get('balance-sheet/export', 'BalanceSheet@export')->name('balance-sheet.export');
    Route::resource('balance-sheet', 'BalanceSheet');
    Route::get('trial-balance/export', 'TrialBalance@export')->name('trial-balance.export');
    Route::resource('trial-balance', 'TrialBalance');

    Route::get('settings', 'Settings@edit');
    Route::post('settings', 'Settings@update');

    Route::get('filter', 'Filter@index')->name('double-entry.filter');
});
