import { Button } from '@/components/ui/button';
import { useAppearance } from '@/hooks/use-appearance';
// eslint-disable-next-line @typescript-eslint/no-unused-vars
import { Moon, Sun, ComputerIcon } from 'lucide-react';

export function ThemeToggle() {
    const { appearance, updateAppearance } = useAppearance();

    const toggleTheme = () => {
        if (appearance === 'light') {
            updateAppearance('dark');
        } else {
            updateAppearance('light');
        }
    };

    const getIcon = () => {
        if (appearance === 'light') {
            return <Sun className="h-4 w-4" />;
        } else {
            return <Moon className="h-4 w-4 opacity-50" />;
        }
    };

    return (
        <Button
            variant="ghost"
            size="sm"
            onClick={toggleTheme}
            className="h-9 w-9 px-0"
            title={`Current theme: ${appearance}. Click to cycle themes.`}
        >
            {getIcon()}
        </Button>
    );
}
