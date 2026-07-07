import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/* ============================================================
 * Thai Calendar Global Utilities
 * ============================================================ */

/**
 * Format any date string/Date object to Thai Buddhist calendar display.
 * @param {string|Date} dateStr  - ISO date string or Date object
 * @param {object}      opts     - Intl.DateTimeFormat options override
 * @returns {string} e.g. "7 ก.ค. 2569"
 */
window.formatThaiDate = function (dateStr, opts = {}) {
    if (!dateStr) return '-';
    try {
        const date = typeof dateStr === 'string' ? new Date(dateStr) : dateStr;
        if (isNaN(date)) return String(dateStr);
        return date.toLocaleDateString('th-TH-u-ca-buddhist', {
            year:  'numeric',
            month: 'short',
            day:   'numeric',
            ...opts,
        });
    } catch (e) {
        return String(dateStr);
    }
};

/**
 * Format with time (for admin tables showing created_at etc.)
 */
window.formatThaiDateTime = function (dateStr) {
    return window.formatThaiDate(dateStr, {
        hour:   '2-digit',
        minute: '2-digit',
    });
};

/**
 * Convert Thai Buddhist year (พ.ศ.) + month + day  →  ISO date string (ค.ศ.)
 * Returns '' if input is incomplete.
 */
window.thaiToIso = function (yearBE, month, day) {
    if (!yearBE || !month || !day) return '';
    const yearAD = parseInt(yearBE, 10) - 543;
    const mm = String(month).padStart(2, '0');
    const dd = String(day).padStart(2, '0');
    return `${yearAD}-${mm}-${dd}`;
};

/**
 * Convert ISO date string  →  { yearBE, month, day }
 */
window.isoToThai = function (isoStr) {
    if (!isoStr) return { yearBE: '', month: '', day: '' };
    const d = new Date(isoStr);
    if (isNaN(d)) return { yearBE: '', month: '', day: '' };
    return {
        yearBE: d.getFullYear() + 543,
        month:  d.getMonth() + 1,
        day:    d.getDate(),
    };
};

// Thai month names
window.thaiMonths = [
    'มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
    'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'
];

/**
 * Alpine.js component factory for a Thai-calendar date picker.
 * Usage in x-data:  thaiDatePicker(isoModelRef, onChangeCallback)
 *
 * Bind two-way via :value / @change on a hidden input, or use the
 * helper methods directly inside the parent Alpine component.
 */
window.thaiDatePickerData = function (getIso, setIso, onChange) {
    const parsed = window.isoToThai(getIso());
    return {
        tdDay:   parsed.day    ? String(parsed.day)   : '',
        tdMonth: parsed.month  ? String(parsed.month) : '',
        tdYear:  parsed.yearBE ? String(parsed.yearBE): '',
        get thaiMonths() { return window.thaiMonths; },
        syncFromIso(iso) {
            const p = window.isoToThai(iso);
            this.tdDay   = p.day    ? String(p.day)   : '';
            this.tdMonth = p.month  ? String(p.month) : '';
            this.tdYear  = p.yearBE ? String(p.yearBE): '';
        },
        commit() {
            const iso = window.thaiToIso(this.tdYear, this.tdMonth, this.tdDay);
            setIso(iso);
            if (typeof onChange === 'function') onChange(iso);
        },
    };
};

Alpine.start();

