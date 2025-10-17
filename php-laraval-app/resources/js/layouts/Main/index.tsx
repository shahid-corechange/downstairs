import { Flex, FlexProps } from "@chakra-ui/react";

import { useFlashToast } from "@/hooks/toast";

import useLayoutStore from "@/stores/layout";

import Navbar from "./components/Navbar";
import Sidebar from "./components/Sidebar";

interface MainLayoutProps {
  children: React.ReactNode;
  content?: FlexProps;
}

const MainLayout = ({ children, content }: MainLayoutProps) => {
  useFlashToast();

  const sidebar = useLayoutStore((state) => state.sidebar);
  const sidebarWidth = useLayoutStore((state) => state.sidebarWidth);

  return (
    <Flex minH="100vh" direction="column">
      <Navbar />
      <Flex flex={1}>
        <Sidebar />
        <Flex
          w={`calc(100% - ${sidebar ? sidebarWidth : 0}px)`}
          direction="column"
          overflowX="clip"
          bg="secondaryGray.50"
          transition={`width 0.3s ${sidebar ? "0.3s" : "0s"}`}
          _dark={{ bg: "gray.900" }}
          {...content}
        >
          {children}
        </Flex>
      </Flex>
    </Flex>
  );
};

export default MainLayout;
