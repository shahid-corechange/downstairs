export interface CursorPagination {
  total: number;
  size: number;
  currentCursor: string | null;
  nextCursor: string | null;
  nextPageUrl: string | null;
  previousCursor: string | null;
  previousPageUrl: string | null;
}

export interface PagePagination {
  size: number;
  total: number;
  currentPage: number;
  lastPage: number;
  firstPageUrl: string | null;
  lastPageUrl: string | null;
  nextPageUrl: string | null;
  previousPageUrl: string | null;
}
