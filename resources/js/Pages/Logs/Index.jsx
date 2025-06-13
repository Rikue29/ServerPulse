import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';

const LogLevels = {
    emergency: { color: 'bg-red-500', text: 'text-white' },
    alert: { color: 'bg-red-400', text: 'text-white' },
    critical: { color: 'bg-red-300', text: 'text-white' },
    error: { color: 'bg-orange-500', text: 'text-white' },
    warning: { color: 'bg-yellow-500', text: 'text-white' },
    notice: { color: 'bg-blue-500', text: 'text-white' },
    info: { color: 'bg-green-500', text: 'text-white' },
    debug: { color: 'bg-gray-500', text: 'text-white' },
};

export default function Index({ logs, servers, filters }) {
    const [search, setSearch] = useState(filters.search || '');
    const [level, setLevel] = useState(filters.level || '');
    const [serverId, setServerId] = useState(filters.server_id || '');

    const handleSearch = (value) => {
        setSearch(value);
        router.get(
            route('logs.index'),
            { search: value, level, server_id: serverId },
            { preserveState: true }
        );
    };

    const handleLevelChange = (value) => {
        setLevel(value);
        router.get(
            route('logs.index'),
            { search, level: value, server_id: serverId },
            { preserveState: true }
        );
    };

    const handleServerChange = (value) => {
        setServerId(value);
        router.get(
            route('logs.index'),
            { search, level, server_id: value },
            { preserveState: true }
        );
    };

    return (
        <AppLayout>
            <Head title="Logs" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex gap-4 mb-6">
                                <Input
                                    type="text"
                                    placeholder="Search logs..."
                                    value={search}
                                    onChange={(e) => handleSearch(e.target.value)}
                                    className="max-w-sm"
                                />
                                <Select
                                    value={level}
                                    onValueChange={handleLevelChange}
                                    className="max-w-xs"
                                >
                                    <option value="">All Levels</option>
                                    {Object.keys(LogLevels).map((level) => (
                                        <option key={level} value={level}>
                                            {level.charAt(0).toUpperCase() + level.slice(1)}
                                        </option>
                                    ))}
                                </Select>
                                <Select
                                    value={serverId}
                                    onValueChange={handleServerChange}
                                    className="max-w-xs"
                                >
                                    <option value="">All Servers</option>
                                    {servers.map((server) => (
                                        <option key={server.id} value={server.id}>
                                            {server.name}
                                        </option>
                                    ))}
                                </Select>
                            </div>

                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Timestamp</TableHead>
                                        <TableHead>Level</TableHead>
                                        <TableHead>Server</TableHead>
                                        <TableHead>Message</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {logs.data.map((log) => (
                                        <TableRow key={log.id}>
                                            <TableCell>
                                                {new Date(log.timestamp).toLocaleString()}
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    className={`${
                                                        LogLevels[log.level]?.color || 'bg-gray-500'
                                                    } ${LogLevels[log.level]?.text || 'text-white'}`}
                                                >
                                                    {log.level}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>{log.server?.name}</TableCell>
                                            <TableCell>{log.message}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>

                            <div className="mt-4">
                                {logs.links.map((link, i) => (
                                    <a
                                        key={i}
                                        href={link.url}
                                        className={`px-3 py-1 mx-1 rounded ${
                                            link.active
                                                ? 'bg-blue-500 text-white'
                                                : 'bg-gray-200'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
} 