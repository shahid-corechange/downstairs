import {
  BreadcrumbItem,
  BreadcrumbLink,
  Breadcrumb as ChakraBreadcrumb,
  BreadcrumbProps as ChakraBreadcrumbProps,
  useConst,
} from "@chakra-ui/react";

import { createBreadcrumb } from "@/utils/menu";

type BreadcrumbProps = Omit<ChakraBreadcrumbProps, "children">;

const Breadcrumb = (props: BreadcrumbProps) => {
  const breadcrumbItems = useConst(createBreadcrumb());

  return (
    <ChakraBreadcrumb spacing="8px" {...props}>
      {breadcrumbItems.map((item, index) => (
        <BreadcrumbItem key={index} fontSize="sm" isCurrentPage>
          <BreadcrumbLink href={"path" in item ? item.path : "#"} fontSize="sm">
            {"title" in item ? item.title : item.group}
          </BreadcrumbLink>
        </BreadcrumbItem>
      ))}
    </ChakraBreadcrumb>
  );
};

export default Breadcrumb;
