import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

export default function AppLayout({ children }) {
    const [isSidebarOpen, setIsSidebarOpen] = useState(true);

    return (
        <div className="min-h-screen bg-gray-100">
            {/* Sidebar */}
            <div className={`fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform ${isSidebarOpen ? 'translate-x-0' : '-translate-x-full'} transition-transform duration-300 ease-in-out`}>
                <div className="flex items-center justify-between h-16 px-4 border-b">
                    <h1 className="text-xl font-semibold">ServerPulse</h1>
                    <button
                        onClick={() => setIsSidebarOpen(false)}
                        className="p-2 rounded-md hover:bg-gray-100"
                    >
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <nav className="p-4">
                    <Link
                        href={route('dashboard')}
                        className="flex items-center px-4 py-2 text-gray-700 rounded-md hover:bg-gray-100"
                    >
                        <svg className="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </Link>
                    <Link
                        href={route('logs.index')}
                        className="flex items-center px-4 py-2 mt-2 text-gray-700 rounded-md hover:bg-gray-100"
                    >
                        <svg className="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Logs
                    </Link>
                </nav>
            </div>

            {/* Main Content */}
            <div className={`${isSidebarOpen ? 'ml-64' : 'ml-0'} transition-all duration-300 ease-in-out`}>
                {/* Top Navigation */}
                <div className="h-16 bg-white shadow-sm">
                    <div className="flex items-center justify-between h-full px-4">
                        <button
                            onClick={() => setIsSidebarOpen(true)}
                            className={`p-2 rounded-md hover:bg-gray-100 ${isSidebarOpen ? 'hidden' : 'block'}`}
                        >
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div className="flex items-center space-x-4">
                            <Button variant="outline" asChild>
                                <Link href={route('logout')} method="post">
                                    Logout
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>

                {/* Page Content */}
                <main className="p-6">
                    {children}
                </main>
            </div>
        </div>
    );
} 