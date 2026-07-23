import { useEffect, useState } from 'react';
import type { TrackerCategory } from '@/types/expense-tracker';

export function useTrackerCategories(): TrackerCategory[] {
    const [categories, setCategories] = useState<TrackerCategory[]>([]);

    useEffect(() => {
        let isMounted = true;
        const controller = new AbortController();

        const loadCategories = async () => {
            try {
                const response = await fetch('/user/categories', {
                    signal: controller.signal,
                    headers: {
                        Accept: 'application/json',
                    },
                });

                if (!response.ok) {
                    return;
                }

                const data = (await response.json()) as TrackerCategory[];

                if (isMounted && Array.isArray(data)) {
                    setCategories(data);
                }
            } catch (error) {
                if (error instanceof DOMException && error.name === 'AbortError') {
                    return;
                }
            }
        };

        void loadCategories();

        return () => {
            isMounted = false;
            controller.abort();
        };
    }, []);

    return categories;
}
