<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import QuickInvoiceLayout from '@/Layouts/QuickInvoiceLayout.vue';
import BlockConfigModal from './Components/BlockConfigModal.vue';
import draggable from 'vuedraggable';
import {
    ArrowLeftIcon, BookmarkIcon, EyeIcon, Squares2X2Icon,
    AdjustmentsHorizontalIcon, SwatchIcon, TrashIcon, PencilIcon,
    ArrowsUpDownIcon, PlusIcon, CheckIcon, SparklesIcon, XMarkIcon,
} from '@heroicons/vue/24/outline';

interface Block {
    id: string;
    type: string;
    position?: { x: number; y: number };
    config?: any;
}

interface Template {
    id?: number;
    name: string;
    description: string;
    layout_json: any;
    field_config: any;
    logo_url?: string;
    primary_color: string;
    secondary_color: string;
    font_family: string;
}

const props = defineProps<{
    template?: Template;
    baseTemplate?: string;
    defaultLayout?: any;
    defaultFieldConfig?: any;
}>();

const isEditing = computed(() => !!props.template);

const activePanel = ref<'components' | 'fields' | 'branding'>('components');
const templateName = ref(props.template?.name || '');
const templateDescription = ref(props.template?.description || '');
const isEditingName = ref(!props.template?.name);

const canvasBlocks = ref<Block[]>(
    props.template?.layout_json?.blocks || props.defaultLayout?.blocks || []
);
const fieldConfig = ref(props.template?.field_config || props.defaultFieldConfig || {});

const logoUrl = ref(props.template?.logo_url || '');
const primaryColor = ref(props.template?.primary_color || '#0d9488');
const secondaryColor = ref(props.template?.secondary_color || '#134e4a');
const fontFamily = ref(props.template?.font_family || 'Inter, sans-serif');

// ─────────────────────────── COMPONENT LIBRARY ───────────────────────────
const componentLibrary = [
    { group: 'Identity', type: 'company-header-centered', label: 'Centered Company Header', description: 'Company name + details centered', color: '#6366f1', icon: '🏢' },
    { group: 'Identity', type: 'header', label: 'Side-by-Side Header', description: 'Logo left, company info right', color: '#6366f1', icon: '↔️' },
    { group: 'Identity', type: 'invoice-title-bar', label: 'Invoice Title Bar', description: 'Bold INVOICE/RECEIPT heading', color: '#7c3aed', icon: '📄' },
    { group: 'Invoice Info', type: 'invoice-meta', label: 'Invoice Meta', description: 'Number, date, due date', color: '#8b5cf6', icon: '📋' },
    { group: 'Invoice Info', type: 'invoice-meta-inline', label: 'Meta Inline', description: 'Compact single-row meta strip', color: '#8b5cf6', icon: '🔢' },
    { group: 'Customer', type: 'customer-details', label: 'Bill To Card', description: 'Modern card-style bill to', color: '#0ea5e9', icon: '👤' },
    { group: 'Customer', type: 'customer-form-lines', label: 'Customer Form Lines', description: 'Classic fill-in-the-blank style', color: '#0ea5e9', icon: '📝' },
    { group: 'Customer', type: 'customer-split', label: 'Bill To / Company Split', description: 'Two-column customer + company', color: '#0ea5e9', icon: '👥' },
    { group: 'Items', type: 'items-table', label: 'Items Table (Modern)', description: 'Colored header, striped rows', color: '#10b981', icon: '📊' },
    { group: 'Items', type: 'items-table-classic', label: 'Items Table (Classic)', description: 'Full-border receipt-style table', color: '#10b981', icon: '🗒️' },
    { group: 'Items', type: 'items-table-minimal', label: 'Items Table (Minimal)', description: 'Underline-only rows, clean', color: '#10b981', icon: '📃' },
    { group: 'Totals & Payment', type: 'totals', label: 'Totals Block', description: 'Subtotal, tax, total', color: '#f59e0b', icon: '💰' },
    { group: 'Totals & Payment', type: 'totals-classic', label: 'Totals Classic', description: 'Bordered classic totals grid', color: '#f59e0b', icon: '🧾' },
    { group: 'Totals & Payment', type: 'payment-details', label: 'Payment Details', description: 'Bank / PayPal payment info', color: '#f97316', icon: '🏦' },
    { group: 'Footer & Extras', type: 'notes-terms', label: 'Notes & Terms', description: 'Terms box with heading bar', color: '#64748b', icon: '📜' },
    { group: 'Footer & Extras', type: 'signature-lines', label: 'Signature Lines', description: 'Received by / Signature lines', color: '#64748b', icon: '✍️' },
    { group: 'Footer & Extras', type: 'thank-you-footer', label: 'Thank You Footer', description: '"Thank you for your business"', color: '#64748b', icon: '🙏' },
    { group: 'Footer & Extras', type: 'text', label: 'Text Block', description: 'Custom paragraph text', color: '#94a3b8', icon: '💬' },
    { group: 'Footer & Extras', type: 'divider', label: 'Divider', description: 'Horizontal separator', color: '#94a3b8', icon: '➖' },
];

const componentGroups = computed(() => {
    const groups: Record<string, typeof componentLibrary> = {};
    componentLibrary.forEach(c => {
        if (!groups[c.group]) groups[c.group] = [];
        groups[c.group].push(c);
    });
    return groups;
});

const getBlockMeta = (type: string) => componentLibrary.find(c => c.type === type);

// ─────────────────────────── TEMPLATE PRESETS ───────────────────────────
interface Preset {
    id: string;
    name: string;
    description: string;
    thumbnail: string;
    primaryColor: string;
    secondaryColor: string;
    fontFamily: string;
    blocks: Array<{ type: string; config?: any }>;
}

const templatePresets: Preset[] = [
    {
        id: 'classic-receipt',
        name: 'Classic Receipt',
        description: 'Traditional receipt with bold header bar and bordered table',
        thumbnail: '🗒️',
        primaryColor: '#1e293b',
        secondaryColor: '#475569',
        fontFamily: 'Arial, sans-serif',
        blocks: [
            { type: 'invoice-title-bar', config: { title: 'INVOICE / RECEIPT', showNumber: true, style: 'bold-bar' } },
            { type: 'company-header-centered', config: { showLogo: true, showAddress: true, showPhone: true, showEmail: true, showWebsite: true } },
            { type: 'customer-form-lines', config: { showName: true, showAddress: true, showPhone: true, showPO: true, showSoldBy: true, showPaymentMethod: true, showDate: true } },
            { type: 'items-table-classic', config: { columns: ['qty', 'description', 'amount'], showSalesTax: true, showTotal: true } },
            { type: 'signature-lines', config: { showReceivedBy: true, showSignature: true } },
            { type: 'thank-you-footer', config: { message: 'THANK YOU FOR YOUR BUSINESS' } },
        ],
    },
    {
        id: 'professional-modern',
        name: 'Professional Modern',
        description: 'Big heading, two-column layout, payment details section',
        thumbnail: '📑',
        primaryColor: '#0d9488',
        secondaryColor: '#134e4a',
        fontFamily: 'Inter, sans-serif',
        blocks: [
            { type: 'invoice-title-bar', config: { title: 'INVOICE', showNumber: false, style: 'large-title' } },
            { type: 'invoice-meta-inline', config: { showInvoiceNumber: true, showDate: true } },
            { type: 'customer-split', config: { showBillTo: true, showCompanyRight: true } },
            { type: 'items-table', config: { columns: ['ser', 'description', 'qty', 'unit_cost', 'amount'], style: 'striped', showBorders: true } },
            { type: 'payment-details', config: { showPaypal: true, showBank: true, label: 'PAYMENT DETAILS' } },
            { type: 'totals', config: { alignment: 'right', showSubtotal: true, showTax: true, showDiscount: true } },
            { type: 'notes-terms', config: { label: 'SPECIAL NOTES AND TERMS', style: 'bar' } },
        ],
    },
    {
        id: 'carbon-copy',
        name: 'Carbon Copy Form',
        description: 'Centered company header with classic bordered customer form',
        thumbnail: '📋',
        primaryColor: '#1e293b',
        secondaryColor: '#374151',
        fontFamily: "'Times New Roman', serif",
        blocks: [
            { type: 'company-header-centered', config: { showLogo: false, showAddress: true, showPhone: true, showEmail: true, showWebsite: true, style: 'large-name' } },
            { type: 'customer-form-lines', config: { showOrderNumber: true, showDate: true, showName: true, showAddress: true, showCity: true, style: 'bordered-box' } },
            { type: 'items-table-classic', config: { columns: ['qty', 'description', 'price', 'amount'], showSalesTax: true, showTotal: true, style: 'full-border' } },
            { type: 'signature-lines', config: { showSignature: true, showPrintName: true } },
        ],
    },
    {
        id: 'minimal-clean',
        name: 'Minimal Clean',
        description: 'Clean, modern invoice with subtle blue accents',
        thumbnail: '✨',
        primaryColor: '#3b82f6',
        secondaryColor: '#1d4ed8',
        fontFamily: "'DM Sans', sans-serif",
        blocks: [
            { type: 'header', config: { showLogo: true, showCompanyInfo: true, alignment: 'space-between' } },
            { type: 'invoice-meta', config: { showTitle: true, titleAlignment: 'left' } },
            { type: 'customer-details', config: { showAddress: true, showPhone: true, showEmail: true } },
            { type: 'items-table-minimal', config: { columns: ['description', 'qty', 'price', 'total'] } },
            { type: 'totals', config: { alignment: 'right', showSubtotal: true, showTax: true, showDiscount: false } },
            { type: 'notes-terms', config: { label: 'Notes', style: 'plain' } },
        ],
    },
    {
        id: 'blank',
        name: 'Start Blank',
        description: 'Empty canvas — drag blocks to build from scratch',
        thumbnail: '➕',
        primaryColor: '#6366f1',
        secondaryColor: '#4f46e5',
        fontFamily: 'Inter, sans-serif',
        blocks: [],
    },
];

const showPresetsModal = ref(canvasBlocks.value.length === 0 && !isEditing.value);

const applyPreset = (preset: Preset) => {
    primaryColor.value = preset.primaryColor;
    secondaryColor.value = preset.secondaryColor;
    fontFamily.value = preset.fontFamily;
    canvasBlocks.value = preset.blocks.map((b, i) => ({
        id: `${b.type}-${Date.now()}-${i}`,
        type: b.type,
        position: { x: 0, y: i },
        config: { ...getDefaultBlockConfig(b.type), ...(b.config || {}) },
    }));
    showPresetsModal.value = false;
    updatePreview();
};

// ─────────────────────────── DEFAULT CONFIGS ───────────────────────────
const getDefaultBlockConfig = (type: string): any => {
    const configs: Record<string, any> = {
        'header': { showLogo: true, showCompanyInfo: true, alignment: 'space-between' },
        'company-header-centered': { showLogo: true, showAddress: true, showPhone: true, showEmail: true, showWebsite: false, style: 'normal' },
        'invoice-title-bar': { title: 'INVOICE', showNumber: true, style: 'bold-bar' },
        'invoice-meta': { showTitle: true, titleAlignment: 'left' },
        'invoice-meta-inline': { showInvoiceNumber: true, showDate: true },
        'customer-details': { showAddress: true, showPhone: false, showEmail: false },
        'customer-form-lines': { showName: true, showAddress: true, showPhone: true, showDate: true, showPO: false, showSoldBy: false, showPaymentMethod: false, showOrderNumber: false, showCity: false, style: 'lines' },
        'customer-split': { showBillTo: true, showCompanyRight: true },
        'items-table': { columns: ['description', 'qty', 'unit_cost', 'amount'], style: 'striped', showBorders: true },
        'items-table-classic': { columns: ['qty', 'description', 'amount'], showSalesTax: true, showTotal: true },
        'items-table-minimal': { columns: ['description', 'qty', 'price', 'total'] },
        'totals': { alignment: 'right', showSubtotal: true, showTax: true, showDiscount: false },
        'totals-classic': { showSalesTax: true },
        'payment-details': { showPaypal: false, showBank: true, label: 'PAYMENT DETAILS' },
        'notes-terms': { label: 'NOTES & TERMS', placeholder: 'Enter notes or terms here...', style: 'bar' },
        'signature-lines': { showReceivedBy: true, showSignature: true, showPrintName: false },
        'thank-you-footer': { message: 'THANK YOU FOR YOUR BUSINESS' },
        'text': { text: 'Enter your text here', alignment: 'left', fontSize: 'text-base' },
        'divider': { style: 'solid', color: '#e2e8f0', thickness: '1px' },
    };
    return configs[type] || {};
};

// ─────────────────────────── DRAG/DROP & CANVAS ───────────────────────────
const dragOptions = { animation: 200, group: 'blocks', disabled: false, ghostClass: 'ghost', handle: '.drag-handle' };

const addBlockToCanvas = (componentType: string) => {
    canvasBlocks.value.push({
        id: `${componentType}-${Date.now()}`,
        type: componentType,
        position: { x: 0, y: canvasBlocks.value.length },
        config: getDefaultBlockConfig(componentType),
    });
    updatePreview();
};

const removeBlock = (index: number) => { canvasBlocks.value.splice(index, 1); updatePreview(); };

const showConfigModal = ref(false);
const editingBlock = ref<Block | null>(null);
const editingBlockIndex = ref(-1);

const openBlockConfig = (block: Block, index: number) => {
    editingBlock.value = { ...block, config: { ...block.config } };
    editingBlockIndex.value = index;
    showConfigModal.value = true;
};
const closeBlockConfig = () => { showConfigModal.value = false; editingBlock.value = null; editingBlockIndex.value = -1; };
const saveBlockConfig = (config: any) => {
    if (editingBlockIndex.value >= 0) {
        canvasBlocks.value[editingBlockIndex.value] = { ...canvasBlocks.value[editingBlockIndex.value], config: { ...config } };
        updatePreview();
    }
    closeBlockConfig();
};

// ─────────────────────────── PREVIEW RENDERER ───────────────────────────
const previewHtml = ref('');

const renderBlock = (block: Block, pc: string): string => {
    const cfg = block.config || {};
    const s = {
        co: 'Your Company', tag: 'Services and Products',
        addr: '111 Main St, Your City, State Zip',
        phone: '(260) 977-000-000', email: 'info@company.com', web: 'www.company.com',
        inv: 'INV-00001',
        date: new Date().toLocaleDateString('en-GB'),
        due: new Date(Date.now() + 30*24*60*60*1000).toLocaleDateString('en-GB'),
        cn: 'Sample Client', cco: 'Acme Corp',
        ca: '456 Client Ave, Lusaka', cp: '+260 966 111 222', ce: 'client@example.com',
    };
    const px = 'padding-left:2rem;padding-right:2rem;';

    switch (block.type) {

        case 'company-header-centered': {
            const lg = cfg.style === 'large-name';
            return `<div style="${px}padding-top:1.5rem;padding-bottom:1rem;text-align:center;border-bottom:2px solid #1e293b;">
                ${cfg.showLogo && logoUrl.value ? `<img src="${logoUrl.value}" style="height:2.5rem;margin-bottom:0.6rem;"><br>` : ''}
                <div style="font-size:${lg?'2rem':'1.4rem'};font-weight:900;letter-spacing:-0.02em;">${s.co}</div>
                ${cfg.showAddress?`<div style="font-size:0.75rem;color:#475569;margin-top:0.15rem;">${s.addr}</div>`:''}
                ${cfg.showPhone?`<div style="font-size:0.8rem;font-weight:600;margin-top:0.1rem;">Phone: ${s.phone}</div>`:''}
                ${cfg.showWebsite?`<div style="font-size:0.75rem;color:#475569;">${s.web}</div>`:''}
                ${cfg.showEmail?`<div style="font-size:0.75rem;color:#475569;">email: ${s.email}</div>`:''}
            </div>`;
        }

        case 'header': {
            const al = cfg.alignment || 'space-between';
            return `<div style="${px}padding-top:1.5rem;padding-bottom:1rem;display:flex;justify-content:${al};align-items:flex-start;gap:1rem;">
                ${cfg.showLogo!==false ? `<div>${logoUrl.value?`<img src="${logoUrl.value}" style="height:2.5rem;">`:`<div style="background:${pc};color:white;font-weight:900;font-size:0.85rem;padding:0.4rem 0.75rem;border-radius:4px;">YOUR LOGO</div>`}</div>` : ''}
                ${cfg.showCompanyInfo!==false ? `<div style="text-align:right;"><div style="font-size:1.1rem;font-weight:800;">${s.co}</div><div style="font-size:0.72rem;color:#64748b;margin-top:0.15rem;">${s.addr}</div><div style="font-size:0.72rem;color:#64748b;">${s.email}</div></div>` : ''}
            </div>`;
        }

        case 'invoice-title-bar': {
            const title = cfg.title || 'INVOICE';
            if (cfg.style === 'large-title') {
                return `<div style="${px}padding-top:1rem;padding-bottom:0.5rem;display:flex;justify-content:space-between;align-items:flex-start;">
                    <div style="font-size:3rem;font-weight:900;color:${pc};letter-spacing:-0.03em;line-height:1;">${title}</div>
                    <div style="border:2px solid #334155;width:80px;height:55px;display:flex;align-items:center;justify-content:center;font-size:0.65rem;color:#94a3b8;text-align:center;padding:0.25rem;">Your logo here</div>
                </div>`;
            }
            return `<div style="background:#1e293b;padding:0.55rem 2rem;display:flex;justify-content:space-between;align-items:center;">
                <div style="color:white;font-size:1.25rem;font-weight:900;letter-spacing:0.02em;">${title}</div>
                ${cfg.showNumber!==false?`<div style="color:white;font-size:0.85rem;font-weight:700;">No : 00001</div>`:''}
            </div>`;
        }

        case 'invoice-meta': {
            return `<div style="${px}padding-top:1rem;padding-bottom:0.75rem;">
                ${cfg.showTitle!==false?`<div style="font-size:1.8rem;font-weight:900;color:${pc};letter-spacing:-0.02em;margin-bottom:0.6rem;">INVOICE</div>`:''}
                <div style="display:flex;gap:2rem;font-size:0.78rem;">
                    <div><div style="color:#94a3b8;font-size:0.62rem;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;margin-bottom:0.1rem;">Invoice No.</div><div style="font-weight:700;">${s.inv}</div></div>
                    <div><div style="color:#94a3b8;font-size:0.62rem;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;margin-bottom:0.1rem;">Issue Date</div><div style="font-weight:700;">${s.date}</div></div>
                    <div><div style="color:#94a3b8;font-size:0.62rem;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;margin-bottom:0.1rem;">Due Date</div><div style="font-weight:700;color:${pc};">${s.due}</div></div>
                </div>
            </div>`;
        }

        case 'invoice-meta-inline':
            return `<div style="${px}padding-top:0.6rem;padding-bottom:0.6rem;display:flex;gap:2rem;font-size:0.82rem;border-bottom:3px solid ${pc};">
                <div><strong>INVOICE #:</strong> ${s.inv}</div>
                <div><strong>Date:</strong> ${s.date}</div>
            </div>`;

        case 'customer-details':
            return `<div style="${px}padding-top:0.75rem;padding-bottom:0.75rem;">
                <div style="background:#f8fafc;border-radius:8px;padding:0.9rem;">
                    <div style="font-size:0.62rem;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;color:#94a3b8;margin-bottom:0.4rem;">Bill To</div>
                    <div style="font-weight:700;font-size:0.88rem;">${s.cn}</div>
                    ${cfg.showAddress!==false?`<div style="font-size:0.75rem;color:#64748b;margin-top:0.15rem;">${s.ca}</div>`:''}
                    ${cfg.showPhone?`<div style="font-size:0.75rem;color:#64748b;">${s.cp}</div>`:''}
                    ${cfg.showEmail?`<div style="font-size:0.75rem;color:#64748b;">${s.ce}</div>`:''}
                </div>
            </div>`;

        case 'customer-split':
            return `<div style="${px}padding-top:0.75rem;padding-bottom:1rem;display:grid;grid-template-columns:1fr 1fr;gap:1rem;border-bottom:3px solid ${pc};">
                <div>
                    <div style="font-weight:800;font-size:0.8rem;color:${pc};margin-bottom:0.3rem;">BILL TO:</div>
                    <div style="font-size:0.78rem;line-height:1.6;color:#334155;">${s.cco}<br>${s.cn}<br>${s.ca}<br>${s.ce}</div>
                </div>
                ${cfg.showCompanyRight?`<div style="text-align:right;">
                    <div style="font-weight:800;font-size:0.9rem;margin-bottom:0.3rem;">${s.co}</div>
                    <div style="font-size:0.75rem;color:#64748b;line-height:1.7;">${s.addr}<br>${s.phone}<br>${s.email}</div>
                </div>`:''}
            </div>`;

        case 'customer-form-lines': {
            const isBordered = cfg.style === 'bordered-box';
            const bx = isBordered ? 'border:1px solid #1e293b;padding:0.75rem;' : '';
            const ln = 'display:block;border:none;border-bottom:1px solid #1e293b;width:100%;margin-bottom:0.6rem;height:1.3rem;';
            return `<div style="${px}padding-top:0.6rem;padding-bottom:0.4rem;">
                <div style="${bx}">
                    ${cfg.showOrderNumber?`<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;font-size:0.72rem;margin-bottom:0.15rem;"><div>Customer's Order No.<span style="${ln}"></span></div><div>Date<span style="${ln}"></span></div></div>`:''}
                    ${cfg.showName!==false?`<div style="font-size:0.72rem;margin-bottom:0.1rem;">Name<span style="${ln}"></span></div>`:''}
                    ${cfg.showAddress!==false?`<div style="font-size:0.72rem;margin-bottom:0.1rem;">Address<span style="${ln}"></span></div>`:''}
                    ${cfg.showCity?`<div style="display:grid;grid-template-columns:3fr 1fr;gap:1rem;font-size:0.72rem;margin-bottom:0.1rem;"><div>City<span style="${ln}"></span></div><div>Zip<span style="${ln}"></span></div></div>`:''}
                    ${cfg.showPhone?`<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;font-size:0.72rem;margin-bottom:0.1rem;"><div>Phone<span style="${ln}"></span></div><div>email<span style="${ln}"></span></div></div>`:''}
                    ${(cfg.showPO||cfg.showSoldBy||cfg.showPaymentMethod)?`
                    <table style="width:100%;border-collapse:collapse;font-size:0.68rem;border:1px solid #1e293b;margin-top:0.4rem;">
                        <tr>
                            ${cfg.showPO?`<th style="border:1px solid #1e293b;padding:0.28rem 0.4rem;text-align:left;">PO #</th>`:''}
                            ${cfg.showSoldBy?`<th style="border:1px solid #1e293b;padding:0.28rem 0.4rem;text-align:left;">Sold By</th>`:''}
                            ${cfg.showPaymentMethod?`<th style="border:1px solid #1e293b;padding:0.28rem 0.4rem;text-align:center;">CASH</th><th style="border:1px solid #1e293b;padding:0.28rem 0.4rem;text-align:center;">CREDIT CARD</th><th style="border:1px solid #1e293b;padding:0.28rem 0.4rem;text-align:center;">CHECK</th>`:''}
                        </tr>
                        <tr>
                            ${cfg.showPO?`<td style="border:1px solid #1e293b;padding:0.4rem;height:1.4rem;"></td>`:''}
                            ${cfg.showSoldBy?`<td style="border:1px solid #1e293b;padding:0.4rem;height:1.4rem;"></td>`:''}
                            ${cfg.showPaymentMethod?`<td style="border:1px solid #1e293b;padding:0.4rem;"></td><td style="border:1px solid #1e293b;padding:0.4rem;"></td><td style="border:1px solid #1e293b;padding:0.4rem;"></td>`:''}
                        </tr>
                    </table>`:''}
                    ${cfg.showDate&&!cfg.showOrderNumber?`<div style="font-size:0.72rem;font-weight:700;margin-top:0.4rem;">DATE: <span style="display:inline-block;border-bottom:1px solid #1e293b;width:110px;"></span></div>`:''}
                </div>
            </div>`;
        }

        case 'items-table': {
            const isStriped = (cfg.style||'striped')==='striped';
            const cols: string[] = cfg.columns || ['description','qty','unit_cost','amount'];
            const hdrs: Record<string,string> = {ser:'SER',description:'DESCRIPTION',qty:'QTY',quantity:'QTY',price:'UNIT PRICE',unit_cost:'UNIT COST',amount:'AMOUNT',total:'TOTAL'};
            const alg: Record<string,string> = {ser:'center',qty:'center',quantity:'center',price:'right',unit_cost:'right',amount:'right',total:'right',description:'left'};
            const hcells = cols.map((c:string,i:number)=>`<th style="padding:0.55rem 0.65rem;text-align:${alg[c]||'left'};color:white;font-weight:700;font-size:0.7rem;${i===0?'border-radius:5px 0 0 5px;':i===cols.length-1?'border-radius:0 5px 5px 0;':''}">${hdrs[c]||c.toUpperCase()}</th>`).join('');
            const sampleRows = [
                ['1','Sample Item 1','2','K500.00','K1,000.00'],
                ['2','Sample Item 2','1','K1,000.00','K1,000.00'],
            ];
            const rows = sampleRows.map((row,ri)=>`<tr style="${isStriped&&ri%2===0?'background:#f8fafc;':''}border-bottom:1px solid #e2e8f0;">
                ${cols.map((c:string,ci:number)=>`<td style="padding:0.55rem 0.65rem;text-align:${alg[c]||'left'};font-size:0.75rem;color:#334155;">${c==='ser'?row[0]:c==='description'?row[1]:c==='qty'||c==='quantity'?row[2]:row[3]}</td>`).join('')}
            </tr>`).join('');
            return `<div style="${px}padding-top:0.75rem;padding-bottom:0.75rem;"><table style="width:100%;border-collapse:collapse;">
                <thead><tr style="background:${pc};">${hcells}</tr></thead>
                <tbody>${rows}</tbody>
            </table></div>`;
        }

        case 'items-table-classic': {
            const cols: string[] = cfg.columns || ['qty','description','amount'];
            const hdrs: Record<string,string> = {qty:'QTY',quantity:'QUANTITY',description:'DESCRIPTION',price:'PRICE',amount:'AMOUNT',total:'TOTAL'};
            const wds: Record<string,string> = {qty:'10%',quantity:'13%',description:'auto',price:'16%',amount:'16%',total:'16%'};
            const alg: Record<string,string> = {qty:'center',quantity:'center',price:'right',amount:'right',total:'right',description:'left'};
            const bd = 'border:1px solid #1e293b;';
            const hcells = cols.map((c:string)=>`<th style="${bd}padding:0.35rem 0.45rem;text-align:${alg[c]||'left'};background:#1e293b;color:white;font-size:0.68rem;width:${wds[c]||'auto'};">${hdrs[c]||c.toUpperCase()}</th>`).join('');
            const emptyRows = Array(9).fill(null).map(()=>`<tr>${cols.map(()=>`<td style="${bd}padding:0.38rem 0.45rem;height:1.45rem;"></td>`).join('')}</tr>`).join('');
            return `<div style="${px}padding-top:0.4rem;padding-bottom:0.4rem;">
                <table style="width:100%;border-collapse:collapse;font-size:0.72rem;">
                    <thead><tr>${hcells}</tr></thead>
                    <tbody>
                        ${emptyRows}
                        ${cfg.showSalesTax?`<tr><td colspan="${cols.length-1}" style="${bd}padding:0.35rem 0.45rem;text-align:right;font-weight:700;font-size:0.68rem;">SALES TAX</td><td style="${bd}padding:0.35rem 0.45rem;"></td></tr>`:''}
                        ${cfg.showTotal!==false?`<tr><td colspan="${cols.length-1}" style="${bd}padding:0.35rem 0.45rem;text-align:right;font-weight:900;font-size:0.7rem;background:#f8fafc;">TOTAL</td><td style="${bd}padding:0.35rem 0.45rem;font-weight:900;background:#f8fafc;"></td></tr>`:''}
                    </tbody>
                </table>
            </div>`;
        }

        case 'items-table-minimal': {
            const cols: string[] = cfg.columns || ['description','qty','price','total'];
            const alg: Record<string,string> = {qty:'center',price:'right',total:'right',description:'left'};
            const hdrs: Record<string,string> = {description:'Description',qty:'Qty',price:'Unit Price',total:'Total'};
            const rows = [['Sample Item 1','2','K500.00','K1,000.00'],['Sample Item 2','1','K1,000.00','K1,000.00']].map((r,ri)=>
                `<tr style="border-bottom:1px solid #f1f5f9;">${cols.map((c:string,ci:number)=>`<td style="padding:0.5rem 0.45rem;text-align:${alg[c]||'left'};color:#334155;font-size:0.75rem;">${r[ci]||''}</td>`).join('')}</tr>`
            ).join('');
            return `<div style="${px}padding-top:0.75rem;padding-bottom:0.75rem;"><table style="width:100%;border-collapse:collapse;">
                <thead><tr style="border-bottom:2px solid ${pc};">${cols.map((c:string)=>`<th style="padding:0.45rem;text-align:${alg[c]||'left'};font-size:0.62rem;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;font-weight:700;">${hdrs[c]||c}</th>`).join('')}</tr></thead>
                <tbody>${rows}</tbody>
            </table></div>`;
        }

        case 'totals': {
            const al = cfg.alignment || 'right';
            const ml = al==='right'?'auto':al==='center'?'auto':'0';
            const mr = al==='right'?'0':al==='center'?'auto':'auto';
            return `<div style="${px}padding-top:0.25rem;padding-bottom:0.75rem;">
                <div style="max-width:240px;margin-left:${ml};margin-right:${mr};">
                    ${cfg.showSubtotal!==false?`<div style="display:flex;justify-content:space-between;padding:0.35rem 0;font-size:0.78rem;color:#64748b;border-bottom:1px solid #e2e8f0;"><span>Subtotal</span><span>K2,000.00</span></div>`:''}
                    ${cfg.showTax!==false?`<div style="display:flex;justify-content:space-between;padding:0.35rem 0;font-size:0.78rem;color:#64748b;border-bottom:1px solid #e2e8f0;"><span>Tax (16%)</span><span>K320.00</span></div>`:''}
                    ${cfg.showDiscount?`<div style="display:flex;justify-content:space-between;padding:0.35rem 0;font-size:0.78rem;color:#ef4444;border-bottom:1px solid #e2e8f0;"><span>Discount</span><span>−K100.00</span></div>`:''}
                    <div style="display:flex;justify-content:space-between;padding:0.5rem 0;font-weight:800;font-size:0.95rem;color:${pc};border-top:2px solid ${pc};margin-top:0.15rem;"><span>Total</span><span>K2,220.00</span></div>
                </div>
            </div>`;
        }

        case 'totals-classic': {
            const bd = 'border:1px solid #1e293b;';
            return `<div style="${px}padding-bottom:0.4rem;display:flex;justify-content:flex-end;">
                <table style="border-collapse:collapse;font-size:0.72rem;min-width:190px;">
                    <tr><td style="${bd}padding:0.32rem 0.65rem;font-weight:700;">SUBTOTAL</td><td style="${bd}padding:0.32rem 0.65rem;width:75px;"></td></tr>
                    ${cfg.showSalesTax!==false?`<tr><td style="${bd}padding:0.32rem 0.65rem;font-weight:700;">SALES TAX</td><td style="${bd}padding:0.32rem 0.65rem;"></td></tr>`:''}
                    <tr style="background:#f8fafc;"><td style="${bd}padding:0.32rem 0.65rem;font-weight:900;">TOTAL</td><td style="${bd}padding:0.32rem 0.65rem;font-weight:900;"></td></tr>
                </table>
            </div>`;
        }

        case 'payment-details':
            return `<div style="${px}padding-top:0.6rem;padding-bottom:0.6rem;">
                <div style="font-weight:800;font-size:0.75rem;color:${pc};margin-bottom:0.5rem;padding-bottom:0.35rem;border-bottom:3px solid ${pc};">${cfg.label||'PAYMENT DETAILS'}</div>
                <div style="font-size:0.75rem;line-height:1.8;color:#475569;">
                    ${cfg.showPaypal?`<div>Paypal: <strong>payments@company.com</strong></div>`:''}
                    ${cfg.showBank!==false?`<div>Bank: <strong>First National Bank</strong></div><div>Business Name: <strong>${s.co}</strong></div><div>Account: <strong>1234567890</strong></div>`:''}
                </div>
            </div>`;

        case 'notes-terms': {
            const isBar = cfg.style !== 'plain';
            return `<div style="${px}padding-top:0.6rem;padding-bottom:0.75rem;">
                ${isBar
                    ? `<div style="background:${pc};color:white;font-weight:800;font-size:0.7rem;padding:0.4rem 0.65rem;letter-spacing:0.04em;">${cfg.label||'SPECIAL NOTES AND TERMS'}</div>
                       <div style="border:1px solid ${pc};border-top:none;padding:0.75rem;min-height:55px;font-size:0.75rem;color:#94a3b8;">${cfg.placeholder||'Enter notes here...'}</div>`
                    : `<div style="font-weight:700;font-size:0.78rem;color:#475569;margin-bottom:0.4rem;">${cfg.label||'Notes'}</div>
                       <div style="border:1px solid #e2e8f0;border-radius:6px;padding:0.65rem;min-height:45px;font-size:0.75rem;color:#94a3b8;">${cfg.placeholder||'Enter notes here...'}</div>`
                }
            </div>`;
        }

        case 'signature-lines': {
            const ln = 'display:inline-block;border-bottom:1px solid #1e293b;flex:1;min-width:90px;height:1.1rem;';
            return `<div style="${px}padding-top:0.75rem;padding-bottom:0.5rem;display:flex;gap:1.5rem;flex-wrap:wrap;">
                ${cfg.showReceivedBy?`<div style="flex:1;min-width:110px;"><div style="display:flex;gap:0.5rem;align-items:flex-end;margin-bottom:0.25rem;"><span style="${ln}"></span></div><div style="font-size:0.6rem;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;color:#64748b;margin-top:0.2rem;">RECEIVED BY</div></div>`:''}
                ${cfg.showSignature?`<div style="flex:1;min-width:110px;"><div style="display:flex;gap:0.5rem;align-items:flex-end;margin-bottom:0.25rem;"><span style="${ln}"></span></div><div style="font-size:0.6rem;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;color:#64748b;margin-top:0.2rem;">SIGNATURE</div></div>`:''}
                ${cfg.showPrintName?`<div style="flex:1;min-width:110px;"><div style="display:flex;gap:0.5rem;align-items:flex-end;margin-bottom:0.25rem;"><span style="${ln}"></span></div><div style="font-size:0.6rem;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;color:#64748b;margin-top:0.2rem;">PRINT NAME</div></div>`:''}
            </div>`;
        }

        case 'thank-you-footer':
            return `<div style="${px}padding-top:0.4rem;padding-bottom:0.75rem;">
                <div style="font-size:0.78rem;font-weight:700;color:#475569;">${cfg.message||'THANK YOU FOR YOUR BUSINESS'}</div>
            </div>`;

        case 'text': {
            const fsMap: Record<string,string> = {'text-xs':'0.72rem','text-sm':'0.85rem','text-base':'1rem','text-lg':'1.1rem','text-xl':'1.25rem'};
            return `<div style="${px}padding-top:0.4rem;padding-bottom:0.4rem;text-align:${cfg.alignment||'left'};font-size:${fsMap[cfg.fontSize||'text-base']||'1rem'};color:#475569;line-height:1.7;">${(cfg.text||'Text block').replace(/\n/g,'<br>')}</div>`;
        }

        case 'divider':
            return `<div style="${px}"><hr style="border:none;border-top:${cfg.thickness||'1px'} ${cfg.style||'solid'} ${cfg.color||'#e2e8f0'};margin:0.6rem 0;"></div>`;

        default:
            return `<div style="${px}padding:0.6rem 0;font-size:0.72rem;color:#94a3b8;">Unknown block: ${block.type}</div>`;
    }
};

const updatePreview = () => {
    const pc = primaryColor.value;
    let html = `<div style="font-family:${fontFamily.value};background:white;color:#1e293b;min-height:100%;">`;
    canvasBlocks.value.forEach(b => { html += renderBlock(b, pc); });
    if (!canvasBlocks.value.length) {
        html += `<div style="text-align:center;padding:4rem 2rem;color:#94a3b8;">
            <div style="font-size:3rem;margin-bottom:1rem;">📄</div>
            <p style="font-size:1rem;font-weight:600;">Your canvas is empty</p>
            <p style="font-size:0.8rem;margin-top:0.4rem;">Choose a preset or add blocks from the left</p>
        </div>`;
    }
    html += '</div>';
    previewHtml.value = html;
};

updatePreview();

// ─────────────────────────── SAVE ───────────────────────────
const isSaving = ref(false);
const showSaveDialog = ref(false);
const saveSuccess = ref(false);

const saveTemplate = () => {
    if (!templateName.value.trim()) { showSaveDialog.value = true; return; }
    isSaving.value = true;
    saveSuccess.value = false;
    const data = {
        name: templateName.value,
        description: templateDescription.value,
        layout_json: { version: '2.0', blocks: canvasBlocks.value },
        field_config: fieldConfig.value,
        logo_url: logoUrl.value,
        primary_color: primaryColor.value,
        secondary_color: secondaryColor.value,
        font_family: fontFamily.value,
    };
    const url = isEditing.value
        ? route('quick-invoice.design-studio.update', props.template!.id)
        : route('quick-invoice.design-studio.store');
    router.post(url, data, {
        onSuccess: () => { showSaveDialog.value = false; saveSuccess.value = true; setTimeout(() => saveSuccess.value = false, 2500); },
        onFinish: () => { isSaving.value = false; },
    });
};

watch([canvasBlocks, primaryColor, secondaryColor, fontFamily, logoUrl], () => updatePreview(), { deep: true });
</script>

<template>
    <QuickInvoiceLayout>
        <Head :title="`${isEditing ? 'Edit' : 'Create'} Template`" />
        <div class="h-screen flex flex-col bg-slate-100 overflow-hidden">

            <!-- Top Bar -->
            <header class="bg-white border-b border-slate-200 px-5 py-2.5 flex items-center gap-3 shrink-0 z-10 shadow-sm">
                <Link :href="route('quick-invoice.design-studio')" class="p-2 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition">
                    <ArrowLeftIcon class="h-4 w-4" />
                </Link>
                <div class="w-px h-5 bg-slate-200"></div>
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <div v-if="!isEditingName" class="flex items-center gap-1.5 group cursor-pointer" @click="isEditingName = true">
                        <h1 class="text-sm font-bold text-slate-800 truncate max-w-xs">{{ templateName || 'Untitled Template' }}</h1>
                        <PencilIcon class="h-3.5 w-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition" />
                    </div>
                    <div v-else class="flex items-center gap-2">
                        <input v-model="templateName" type="text" placeholder="Template name…"
                            class="text-sm font-bold border border-indigo-300 rounded-lg px-2.5 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500 w-52"
                            autofocus @keydown.enter="isEditingName = false" @blur="isEditingName = false" />
                        <button @click="isEditingName = false" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg transition"><CheckIcon class="h-4 w-4" /></button>
                    </div>
                    <span class="text-xs text-slate-400 shrink-0">{{ isEditing ? '· Editing' : '· New template' }}</span>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button @click="showPresetsModal = true" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                        <SparklesIcon class="h-4 w-4 text-amber-500" /> Presets
                    </button>
                    <span v-if="saveSuccess" class="text-xs text-emerald-600 font-bold flex items-center gap-1"><CheckIcon class="h-3.5 w-3.5" />Saved!</span>
                    <span class="text-xs text-slate-400">{{ canvasBlocks.length }} block{{ canvasBlocks.length !== 1 ? 's' : '' }}</span>
                    <button @click="saveTemplate" :disabled="isSaving"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 disabled:opacity-60 transition shadow-sm shadow-indigo-200">
                        <BookmarkIcon class="h-4 w-4" />{{ isSaving ? 'Saving…' : 'Save Template' }}
                    </button>
                </div>
            </header>

            <div class="flex flex-1 overflow-hidden">

                <!-- Left Sidebar -->
                <aside class="w-60 bg-slate-900 flex flex-col shrink-0 overflow-hidden">
                    <div class="flex border-b border-slate-700/50">
                        <button v-for="tab in [{id:'components',icon:Squares2X2Icon,label:'Blocks'},{id:'fields',icon:AdjustmentsHorizontalIcon,label:'Fields'},{id:'branding',icon:SwatchIcon,label:'Brand'}]"
                            :key="tab.id" @click="activePanel = tab.id as any"
                            :class="['flex-1 flex flex-col items-center gap-1 py-3 text-[10px] font-semibold transition-all border-b-2',activePanel===tab.id?'text-indigo-400 border-indigo-400 bg-slate-800/50':'text-slate-500 hover:text-slate-300 border-transparent']">
                            <component :is="tab.icon" class="h-4 w-4"/>{{ tab.label }}
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 custom-scrollbar">

                        <!-- Blocks -->
                        <template v-if="activePanel === 'components'">
                            <div v-for="(blocks, group) in componentGroups" :key="group" class="mb-4">
                                <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest px-1 mb-1.5">{{ group }}</p>
                                <div class="space-y-1">
                                    <button v-for="c in blocks" :key="c.type" @click="addBlockToCanvas(c.type)"
                                        class="w-full flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-700/60 transition group text-left border border-transparent hover:border-slate-600/50">
                                        <div class="w-6 h-6 rounded-lg flex items-center justify-center text-sm shrink-0 group-hover:scale-110 transition-transform" :style="{background:c.color+'22'}">{{ c.icon }}</div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[11px] font-semibold text-slate-200 leading-tight">{{ c.label }}</p>
                                            <p class="text-[9px] text-slate-500 leading-tight truncate">{{ c.description }}</p>
                                        </div>
                                        <PlusIcon class="h-3 w-3 text-slate-600 group-hover:text-indigo-400 shrink-0 transition-colors"/>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Fields -->
                        <template v-if="activePanel === 'fields'">
                            <p class="text-xs text-slate-500 px-1 pb-2 font-medium">Toggle invoice fields</p>
                            <div v-if="!Object.keys(fieldConfig).length" class="text-xs text-slate-600 text-center py-8">No fields configured</div>
                            <div v-for="(config, key) in fieldConfig" :key="key" class="flex items-center gap-3 px-2.5 py-2 rounded-xl hover:bg-slate-800 transition">
                                <label class="relative cursor-pointer shrink-0">
                                    <input type="checkbox" v-model="config.enabled" :disabled="config.required" class="sr-only peer"/>
                                    <div class="w-8 h-4 bg-slate-600 peer-checked:bg-indigo-500 rounded-full transition-colors peer-disabled:opacity-40"></div>
                                    <div class="absolute top-0.5 left-0.5 w-3 h-3 bg-white rounded-full transition-transform peer-checked:translate-x-4 shadow-sm"></div>
                                </label>
                                <span class="text-xs font-medium text-slate-300">{{ config.label }}<span v-if="config.required" class="text-red-400 ml-0.5">*</span></span>
                            </div>
                        </template>

                        <!-- Branding -->
                        <template v-if="activePanel === 'branding'">
                            <div class="space-y-4">
                                <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest px-1">Appearance</p>
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Logo URL</label>
                                    <input type="text" v-model="logoUrl" placeholder="https://example.com/logo.png"
                                        class="w-full px-2.5 py-2 bg-slate-800 border border-slate-700 text-slate-200 placeholder-slate-600 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500"/>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Primary Color</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" v-model="primaryColor" class="w-9 h-9 rounded-xl border-2 border-slate-600 cursor-pointer bg-transparent p-0.5 shrink-0"/>
                                        <input type="text" v-model="primaryColor" class="flex-1 px-2.5 py-2 bg-slate-800 border border-slate-700 text-slate-200 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500"/>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Secondary Color</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" v-model="secondaryColor" class="w-9 h-9 rounded-xl border-2 border-slate-600 cursor-pointer bg-transparent p-0.5 shrink-0"/>
                                        <input type="text" v-model="secondaryColor" class="flex-1 px-2.5 py-2 bg-slate-800 border border-slate-700 text-slate-200 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500"/>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Font Family</label>
                                    <select v-model="fontFamily" class="w-full px-2.5 py-2 bg-slate-800 border border-slate-700 text-slate-200 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        <option value="Inter, sans-serif">Inter</option>
                                        <option value="Roboto, sans-serif">Roboto</option>
                                        <option value="'Open Sans', sans-serif">Open Sans</option>
                                        <option value="Lato, sans-serif">Lato</option>
                                        <option value="Poppins, sans-serif">Poppins</option>
                                        <option value="Montserrat, sans-serif">Montserrat</option>
                                        <option value="'DM Sans', sans-serif">DM Sans</option>
                                        <option value="Arial, sans-serif">Arial</option>
                                        <option value="'Times New Roman', serif">Times New Roman</option>
                                        <option value="Georgia, serif">Georgia</option>
                                    </select>
                                </div>
                                <div class="flex gap-2 pt-1">
                                    <div class="flex-1 h-6 rounded-lg border border-slate-700 transition-colors" :style="{background:primaryColor}"></div>
                                    <div class="flex-1 h-6 rounded-lg border border-slate-700 transition-colors" :style="{background:secondaryColor}"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </aside>

                <!-- Canvas -->
                <main class="flex-1 flex flex-col overflow-hidden">
                    <div class="flex-1 overflow-y-auto p-6">
                        <div class="max-w-2xl mx-auto">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h2 class="text-sm font-bold text-slate-700">Canvas</h2>
                                    <p class="text-xs text-slate-400">Drag to reorder · ✏️ to configure each block</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span v-if="canvasBlocks.length" class="text-xs bg-indigo-50 text-indigo-600 font-bold px-2.5 py-1 rounded-full border border-indigo-100">
                                        {{ canvasBlocks.length }} block{{ canvasBlocks.length !== 1 ? 's' : '' }}
                                    </span>
                                    <button v-if="canvasBlocks.length" @click="showPresetsModal = true" class="text-xs text-slate-400 hover:text-slate-600 px-2 py-1 rounded-lg hover:bg-slate-100 transition">↺ Change preset</button>
                                </div>
                            </div>

                            <div class="rounded-2xl border-2 border-dashed transition-all" :class="canvasBlocks.length?'border-slate-200 bg-white p-4 shadow-sm':'border-slate-200 bg-slate-50 p-4'">
                                <draggable v-model="canvasBlocks" v-bind="dragOptions" item-key="id" class="space-y-2">
                                    <template #item="{ element, index }">
                                        <div class="group flex items-center gap-3 px-3.5 py-3 bg-white border border-slate-200 rounded-xl hover:border-indigo-300 hover:shadow-sm transition-all"
                                            :style="{borderLeftColor:getBlockMeta(element.type)?.color||'#94a3b8',borderLeftWidth:'3px'}">
                                            <div class="drag-handle cursor-grab active:cursor-grabbing text-slate-300 hover:text-slate-500 transition-colors shrink-0"><ArrowsUpDownIcon class="h-4 w-4"/></div>
                                            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-sm shrink-0" :style="{background:(getBlockMeta(element.type)?.color||'#94a3b8')+'18'}">
                                                {{ getBlockMeta(element.type)?.icon || '📦' }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-slate-800 leading-tight">{{ getBlockMeta(element.type)?.label || element.type }}</p>
                                                <p class="text-xs text-slate-400 leading-tight mt-0.5 truncate">{{ getBlockMeta(element.type)?.description }}</p>
                                            </div>
                                            <span class="text-[10px] font-bold text-slate-300 w-5 text-center shrink-0">{{ index + 1 }}</span>
                                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                                                <button @click="openBlockConfig(element, index)" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Configure"><PencilIcon class="h-3.5 w-3.5"/></button>
                                                <button @click="removeBlock(index)" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Remove"><TrashIcon class="h-3.5 w-3.5"/></button>
                                            </div>
                                        </div>
                                    </template>
                                    <template #footer>
                                        <div v-if="canvasBlocks.length === 0" class="py-14 flex flex-col items-center text-center pointer-events-none select-none">
                                            <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mb-4"><Squares2X2Icon class="h-8 w-8 text-slate-300"/></div>
                                            <p class="text-sm font-semibold text-slate-400">Canvas is empty</p>
                                            <p class="text-xs text-slate-300 mt-1">Pick a preset above, or add blocks from the sidebar</p>
                                        </div>
                                    </template>
                                </draggable>
                            </div>
                        </div>
                    </div>
                </main>

                <!-- Live Preview -->
                <aside class="w-80 bg-white border-l border-slate-200 flex flex-col shrink-0 overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2"><EyeIcon class="h-4 w-4 text-slate-400"/><h3 class="text-sm font-bold text-slate-700">Live Preview</h3></div>
                        <span class="text-[10px] text-slate-400 bg-slate-50 px-2 py-0.5 rounded-full border border-slate-200">Sample data</span>
                    </div>
                    <div class="flex-1 overflow-y-auto bg-slate-100 p-3">
                        <div class="bg-white shadow-xl rounded-lg overflow-hidden" style="font-size:0;line-height:0;">
                            <div style="transform:scale(0.58);transform-origin:top left;width:172.4%;font-size:initial;line-height:initial;">
                                <div v-html="previewHtml" style="min-height:520px;"></div>
                            </div>
                            <div style="height:380px;"></div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <!-- ─── Presets Modal ─── -->
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showPresetsModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-6"
                @click.self="canvasBlocks.length > 0 && (showPresetsModal = false)">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Choose a Template Preset</h2>
                            <p class="text-sm text-slate-500 mt-0.5">Start from a pre-built layout and customize it to your needs</p>
                        </div>
                        <button v-if="canvasBlocks.length > 0" @click="showPresetsModal = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition"><XMarkIcon class="h-5 w-5"/></button>
                    </div>
                    <div class="grid grid-cols-2 gap-4 p-6 overflow-y-auto">
                        <button v-for="preset in templatePresets" :key="preset.id" @click="applyPreset(preset)"
                            class="group text-left p-5 border-2 border-slate-200 rounded-2xl hover:border-indigo-400 hover:bg-indigo-50/40 transition-all hover:shadow-md">
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 group-hover:bg-indigo-100 flex items-center justify-center text-2xl transition-colors shrink-0">{{ preset.thumbnail }}</div>
                                <div>
                                    <p class="font-bold text-slate-800 text-sm">{{ preset.name }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">{{ preset.description }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="w-3.5 h-3.5 rounded-full border border-slate-200" :style="{background:preset.primaryColor}"></div>
                                <div class="w-3.5 h-3.5 rounded-full border border-slate-200" :style="{background:preset.secondaryColor}"></div>
                                <span class="text-[10px] text-slate-400 ml-0.5">{{ preset.blocks.length }} block{{ preset.blocks.length !== 1 ? 's' : '' }}</span>
                                <span class="ml-auto text-[10px] font-bold text-indigo-500 opacity-0 group-hover:opacity-100 transition">Use this →</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Block Config Modal -->
        <BlockConfigModal :block="editingBlock" :show="showConfigModal" @close="closeBlockConfig" @save="saveBlockConfig"/>

        <!-- Save Dialog -->
        <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-100 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showSaveDialog" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="showSaveDialog = false">
                <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
                    <h3 class="text-lg font-bold text-slate-900 mb-1">Name your template</h3>
                    <p class="text-sm text-slate-500 mb-5">Give your template a name before saving.</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Template Name <span class="text-red-500">*</span></label>
                            <input type="text" v-model="templateName" placeholder="e.g. Classic Receipt, Client Invoice…" autofocus
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"/>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Description <span class="text-slate-400 font-normal normal-case">(optional)</span></label>
                            <textarea v-model="templateDescription" placeholder="When to use this template…" rows="2"
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"></textarea>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button @click="showSaveDialog = false" class="flex-1 px-4 py-2.5 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition">Cancel</button>
                        <button @click="saveTemplate" :disabled="!templateName.trim() || isSaving"
                            class="flex-1 px-4 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 disabled:opacity-50 transition shadow-sm shadow-indigo-200">
                            {{ isSaving ? 'Saving…' : 'Save Template' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

    </QuickInvoiceLayout>
</template>

<style scoped>
.ghost { opacity: 0.35; background: #eef2ff; border-color: #a5b4fc !important; border-radius: 0.75rem; }
.drag-handle { touch-action: none; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 2px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
</style>