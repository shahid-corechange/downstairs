import BaseModal, { BaseModalProps } from "./Base";
import ExpandableModal, { ExpandableModalProps } from "./Expandable";

export type ModalProps = BaseModalProps | ExpandableModalProps;

const Modal = (props: ModalProps) => {
  if ("isExpanded" in props) {
    return <ExpandableModal {...props} />;
  }

  return <BaseModal {...props} />;
};

export default Modal;
