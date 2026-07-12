import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { useAutoSave } from '../useAutoSave';

describe('useAutoSave', () => {
    beforeEach(() => {
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    it('starts in idle status', () => {
        const autoSave = useAutoSave({ onSave: async () => {} });
        expect(autoSave.status.value).toBe('idle');
        expect(autoSave.isDirty.value).toBe(false);
    });

    it('markDirty sets status to pending', () => {
        const autoSave = useAutoSave({ onSave: async () => {} });
        autoSave.markDirty();
        expect(autoSave.status.value).toBe('pending');
        expect(autoSave.isDirty.value).toBe(true);
    });

    it('save calls onSave callback and sets status to saved', async () => {
        const onSave = vi.fn().mockResolvedValue(undefined);
        const onSuccess = vi.fn();
        const autoSave = useAutoSave({ onSave, onSuccess });

        autoSave.markDirty();
        const result = await autoSave.save();

        expect(result).toBe(true);
        expect(onSave).toHaveBeenCalledOnce();
        expect(autoSave.status.value).toBe('saved');
        expect(autoSave.isDirty.value).toBe(false);
        expect(onSuccess).toHaveBeenCalledOnce();
    });

    it('save returns false when onSave throws', async () => {
        const onSave = vi.fn().mockRejectedValue(new Error('Network error'));
        const onError = vi.fn();
        const autoSave = useAutoSave({ onSave, onError });

        autoSave.markDirty();
        const result = await autoSave.save();

        expect(result).toBe(false);
        expect(autoSave.status.value).toBe('error');
        expect(autoSave.error.value).toBe('Network error');
        expect(onError).toHaveBeenCalledOnce();
    });

    it('markDirty schedules auto-save after delay', () => {
        const onSave = vi.fn().mockResolvedValue(undefined);
        const autoSave = useAutoSave({ delay: 30000, onSave });

        autoSave.markDirty();
        expect(autoSave.status.value).toBe('pending');

        vi.advanceTimersByTime(30000);

        expect(onSave).toHaveBeenCalledOnce();
    });

    it('cancel aborts pending save', () => {
        const onSave = vi.fn();
        const autoSave = useAutoSave({ onSave });

        autoSave.markDirty();
        expect(autoSave.status.value).toBe('pending');

        autoSave.cancel();
        expect(autoSave.status.value).toBe('idle');

        vi.advanceTimersByTime(30000);
        expect(onSave).not.toHaveBeenCalled();
    });

    it('reset clears all state', async () => {
        const onSave = vi.fn().mockResolvedValue(undefined);
        const autoSave = useAutoSave({ onSave });

        autoSave.markDirty();
        await autoSave.save();
        expect(autoSave.status.value).toBe('saved');

        autoSave.reset();
        expect(autoSave.status.value).toBe('idle');
        expect(autoSave.isDirty.value).toBe(false);
        expect(autoSave.error.value).toBeNull();
    });

    it('returns true from save when nothing is dirty', async () => {
        const onSave = vi.fn().mockResolvedValue(undefined);
        const autoSave = useAutoSave({ onSave });

        const result = await autoSave.save();
        expect(result).toBe(true);
        expect(onSave).not.toHaveBeenCalled();
    });

    it('consecutive markDirty calls reset the timer', () => {
        const onSave = vi.fn();
        const autoSave = useAutoSave({ delay: 30000, onSave });

        autoSave.markDirty();
        vi.advanceTimersByTime(15000);
        autoSave.markDirty();
        vi.advanceTimersByTime(15000);

        // Only 15+15 = 30s have passed since last markDirty
        expect(onSave).not.toHaveBeenCalled();

        vi.advanceTimersByTime(15000);
        expect(onSave).toHaveBeenCalledOnce();
    });
});
