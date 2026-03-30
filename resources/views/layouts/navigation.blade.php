<nav class="mt-6 px-4 space-y-2">

    <a href="{{ route('dashboard') }}"
       class="flex items-center px-4 py-2 rounded-lg transition
        {{ request()->routeIs('dashboard')
            ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold'
            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
        Dashboard
    </a>

    <a href="{{ route('transactions.index') }}"
       class="flex items-center px-4 py-2 rounded-lg transition
        {{ request()->routeIs('transactions.*')
            ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold'
            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
        Transactions
    </a>

    <a href="{{ route('accounts.index') }}"
       class="flex items-center px-4 py-2 rounded-lg transition
        {{ request()->routeIs('accounts.*')
            ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold'
            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
        Accounts
    </a>

    <a href="{{ route('budgets.index') }}"
       class="flex items-center px-4 py-2 rounded-lg transition
        {{ request()->routeIs('budgets.*')
            ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold'
            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
        Budgets
    </a>
</nav>
