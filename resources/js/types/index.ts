export interface BreadcrumbItemType {
    title: string;
    href?: string;
    current?: boolean;
}

export interface NavItem {
    title: string;
    href?: string;
    icon?: any;
    items?: NavItem[];
    current?: boolean;
}