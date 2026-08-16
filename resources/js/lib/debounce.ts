export function useDebounceFn<TArgs extends unknown[]>(
    callback: (...args: TArgs) => void,
    delay = 400,
) {
    let timeoutId: ReturnType<typeof window.setTimeout> | undefined;

    const debounced = (...args: TArgs) => {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(() => callback(...args), delay);
    };

    debounced.cancel = () => window.clearTimeout(timeoutId);
    debounced.flush = (...args: TArgs) => {
        window.clearTimeout(timeoutId);
        callback(...args);
    };

    return debounced;
}
