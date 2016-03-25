import json
import sys
import getopt


def main():
	module_file, semester_file = load_args()
	mod_dict = simplify(module_file)
	mod_dict = include_semester_info(mod_dict, semester_file)
	create_json_file(mod_dict)


def load_args():
	module_file = semester_file = None

	try:
		opts, args = getopt.getopt(sys.argv[1:], 'm:s:')
	except getopt.GetoptError, err:
		usage()
		sys.exit(2)
	for o, a in opts:
		if o == '-m':
			module_file = a
		elif o == '-s':
			semester_file = a
		else:
			assert False, "unhandled option"
	if module_file is None:
		usage()
		sys.exit(2)
	return module_file, semester_file


def usage():
	"""Prints the proper format for calling this script."""
	print "usage: " + sys.argv[0] + " -m module_file"


def simplify(module_file):
	"""wanna get "ModuleCode", "ModuleTitle", "ModuleCredit", "ParsedPreclusion", "Prerequisite", "Corequisite"
	TODO Semesters
	TODO Parse prereqs
	list of dicts -> dict of dicts

	:param module_file:
	:return:
	"""
	with open(module_file) as json_file:
		modules = json.load(json_file)
		mod_dict = {}

		for module in modules:
			keys = module.keys()
			for key in keys:
				if key not in {"ModuleCode", "ModuleTitle", "ModuleCredit", "ParsedPreclusion", "Prerequisite", "Corequisite"}:
					module.pop(key, None)

			# Shorten name of keys
			module["NM"] = module.pop("ModuleTitle")
			module["MC"] = module.pop("ModuleCredit")
			temp = module.pop("ParsedPreclusion", None)
			if temp is not None:
				module["PC"] = temp
			temp = module.pop("Prerequisite", None)
			if temp is not None:
				module["PR"] = temp
			temp = module.pop("Corequisite", None)
			if temp is not None:
				module["CR"] = temp

			mod_dict[module.pop("ModuleCode")] = module

		return mod_dict


def include_semester_info(mod_dict, semester_file):
	with open(semester_file) as json_file:
		mod_sems = json.load(json_file)

		for module in mod_sems:
			mod_dict[str(module["ModuleCode"])]["SM"] = module["Semesters"]

		return mod_dict


def create_json_file(modules):
	module_file = file("simplified.json", 'w')
	json.dump(modules, module_file)
	module_file.close()


if __name__ == "__main__":
	main()
